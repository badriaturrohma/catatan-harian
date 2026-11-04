<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CorrectionRequest;
use App\Models\HolidayPeriod;
use App\Models\Journey;
use App\Models\JourneyEvent;
use App\Models\OfficerAssignment;
use App\Models\Permit;
use App\Models\QrCredential;
use App\Models\Santri;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JourneyService
{
    public function __construct(
        protected AlertService $alertService
    ) {}

    /**
     * Resolve a QR token for an officer assignment
     * Returns minimal santri details and valid allowable activities
     */
    public function resolveQrToken(string $token, int $assignmentId, User $officer): array
    {
        $assignment = OfficerAssignment::with('location')->findOrFail($assignmentId);

        if ($assignment->user_id !== $officer->id || ! $assignment->isValidNow()) {
            throw ValidationException::withMessages([
                'assignment' => ['Penugasan petugas tidak aktif atau lokasi tidak sesuai.'],
            ]);
        }

        $tokenHash = hash('sha256', trim($token));
        $credential = QrCredential::with('santri.campus', 'santri.program', 'santri.room')
            ->where('token_hash', $tokenHash)
            ->where('is_active', true)
            ->first();

        if (! $credential || ! $credential->santri || ! $credential->santri->is_active) {
            throw ValidationException::withMessages([
                'qr' => ['QR santri tidak valid atau telah dicabut.'],
            ]);
        }

        $santri = $credential->santri;
        $activeJourney = Journey::where('santri_id', $santri->id)
            ->whereNotIn('status', ['selesai', 'rekonsiliasi'])
            ->latest('started_at')
            ->first();

        $activePermit = Permit::where('santri_id', $santri->id)
            ->where('status', 'approved')
            ->where('start_time', '<=', now()->addHours(2))
            ->where('deadline', '>=', now()->subHours(2))
            ->latest('start_time')
            ->first();

        $locationType = $assignment->location->type; // 'pondok' or 'kampus'
        $allowedActivities = $this->determineAllowedActivities($activeJourney, $locationType, $activePermit);

        return [
            'credential_id' => $credential->id,
            'santri' => [
                'id' => $santri->id,
                'niup' => $santri->niup,
                'name' => $santri->name,
                'gender' => $santri->gender,
                'photo' => $santri->photo,
                'campus' => $santri->campus?->name,
                'program' => $santri->program?->name,
                'room' => $santri->room?->name,
            ],
            'location' => [
                'id' => $assignment->location->id,
                'name' => $assignment->location->name,
                'type' => $assignment->location->type,
            ],
            'active_journey' => $activeJourney ? [
                'id' => $activeJourney->id,
                'type' => $activeJourney->type,
                'status' => $activeJourney->status,
                'started_at' => $activeJourney->started_at->format('d/m/Y H:i'),
            ] : null,
            'active_permit' => $activePermit ? [
                'id' => $activePermit->id,
                'destination_type' => $activePermit->destination_type,
                'destination_name' => $activePermit->destination_name,
                'deadline' => $activePermit->deadline->format('d/m/Y H:i'),
            ] : null,
            'allowed_activities' => $allowedActivities,
        ];
    }

    /**
     * Determine which activities are valid given the current journey state and location
     */
    public function determineAllowedActivities(?Journey $journey, string $locationType, ?Permit $permit = null): array
    {
        $activities = [];

        if (! $journey) {
            // No open journey: only Keluar Pondok is possible at Pos Pondok
            if ($locationType === 'pondok') {
                $activities[] = [
                    'code' => 'keluar_pondok',
                    'label' => 'Keluar Pondok',
                    'description' => $permit ? "Keberangkatan berdasarkan izin ({$permit->destination_name})" : 'Keberangkatan reguler menuju kampus',
                ];
            }

            return $activities;
        }

        // We have an active journey
        $isNonCampusPermit = ($journey->type === 'izin' && $journey->permit && ! $journey->permit->isKampus());

        if ($isNonCampusPermit) {
            // Non-campus permit flow: keluar_pondok -> kembali_pondok
            if ($locationType === 'pondok') {
                $activities[] = [
                    'code' => 'kembali_pondok',
                    'label' => 'Kembali ke Pondok',
                    'description' => 'Kepulangan dari izin luar kampus',
                ];
            }

            return $activities;
        }

        // 4-stage flow
        switch ($journey->status) {
            case 'menuju_kampus':
                if ($locationType === 'kampus') {
                    $activities[] = [
                        'code' => 'tiba_kampus',
                        'label' => 'Tiba di Kampus',
                        'description' => 'Konfirmasi kedatangan di pos kampus',
                    ];
                }
                break;

            case 'di_kampus':
                if ($locationType === 'kampus') {
                    $activities[] = [
                        'code' => 'keluar_kampus',
                        'label' => 'Keluar Kampus',
                        'description' => 'Konfirmasi kepulangan menuju pondok',
                    ];
                }
                break;

            case 'menuju_pondok':
                if ($locationType === 'pondok') {
                    $activities[] = [
                        'code' => 'kembali_pondok',
                        'label' => 'Kembali ke Pondok',
                        'description' => 'Konfirmasi tiba kembali di pondok',
                    ];
                }
                break;
        }

        return $activities;
    }

    /**
     * Record an event via QR scan (Server timestamp enforced, Idempotent by request_id)
     */
    public function recordEvent(array $data, User $actor): array
    {
        $requestId = $data['request_id'];

        // Idempotency check: if request_id already exists, return existing event
        $existingEvent = JourneyEvent::with('journey.santri')->where('request_id', $requestId)->first();
        if ($existingEvent) {
            return [
                'status' => 'duplicate',
                'event' => $existingEvent,
                'journey' => $existingEvent->journey,
                'message' => 'Event dengan request ID ini telah tercatat sebelumnya.',
            ];
        }

        return DB::transaction(function () use ($data, $actor, $requestId) {
            $assignment = OfficerAssignment::with('location')->lockForUpdate()->findOrFail($data['assignment_id']);

            if ($assignment->user_id !== $actor->id || ! $assignment->isValidNow()) {
                throw new Exception('Penugasan petugas tidak aktif atau telah kedaluwarsa.');
            }

            $credential = QrCredential::with('santri.campus')->findOrFail($data['credential_id']);
            $santri = $credential->santri;

            if (! $santri->is_active) {
                throw new Exception('Santri dalam status tidak aktif.');
            }

            $activity = $data['activity'];
            $now = Carbon::now('Asia/Jakarta');

            // Find or initialize journey with lock
            $journey = Journey::where('santri_id', $santri->id)
                ->whereNotIn('status', ['selesai', 'rekonsiliasi'])
                ->lockForUpdate()
                ->latest('started_at')
                ->first();

            // Validate activity sequence and holiday rules
            if ($activity === 'keluar_pondok') {
                if ($journey) {
                    throw new Exception('Santri masih memiliki perjalanan aktif yang belum selesai.');
                }

                // Check holiday and cutoff rules
                $activePermit = Permit::where('santri_id', $santri->id)
                    ->where('status', 'approved')
                    ->where('start_time', '<=', $now->copy()->addHours(2))
                    ->where('deadline', '>=', $now)
                    ->lockForUpdate()
                    ->first();

                if ($santri->campus_id) {
                    $holiday = HolidayPeriod::isHolidayForCampus($santri->campus_id, $now->toDateString());
                    if ($holiday && ! $activePermit) {
                        $cutoff = Carbon::createFromTimeString($holiday->departure_cutoff_time, 'Asia/Jakarta');
                        if ($now->greaterThan($cutoff)) {
                            throw new Exception("Kampus {$santri->campus->name} sedang libur (batas keluar {$holiday->departure_cutoff_time}). Keberangkatan ditolak tanpa izin resmi.");
                        }
                    }
                }

                // Create new journey
                $journeyType = $activePermit ? 'izin' : 'reguler';
                $journey = Journey::create([
                    'santri_id' => $santri->id,
                    'type' => $journeyType,
                    'operational_date' => $now->toDateString(),
                    'status' => 'menuju_kampus',
                    'permit_id' => $activePermit?->id,
                    'started_at' => $now,
                    'organization_snapshot' => $santri->getOrganizationSnapshot(),
                ]);

                if ($activePermit) {
                    $activePermit->update(['status' => 'used']);
                }
            } else {
                if (! $journey) {
                    throw new Exception('Tidak ada perjalanan aktif untuk santri ini. Perlu dilakukan pencatatan keberangkatan terlebih dahulu.');
                }

                // Verify sequence
                $allowed = $this->determineAllowedActivities($journey, $assignment->location->type, $journey->permit);
                $isAllowed = collect($allowed)->contains('code', $activity);

                if (! $isAllowed) {
                    throw new Exception("Aktivitas {$activity} tidak valid untuk status perjalanan saat ini ({$journey->status}) di pos ini.");
                }

                // Update journey status based on activity
                switch ($activity) {
                    case 'tiba_kampus':
                        $journey->status = 'di_kampus';
                        break;
                    case 'keluar_kampus':
                        $journey->status = 'menuju_pondok';
                        break;
                    case 'kembali_pondok':
                        $journey->status = 'selesai';
                        $journey->completed_at = $now;
                        if ($journey->permit) {
                            $journey->permit->update(['status' => 'completed']);
                        }
                        break;
                }
                $journey->save();
            }

            // Create Journey Event
            $event = JourneyEvent::create([
                'journey_id' => $journey->id,
                'activity' => $activity,
                'occurred_at' => $now,
                'recorded_at' => $now,
                'actor_id' => $actor->id,
                'location_id' => $assignment->location_id,
                'method' => 'qr_scan',
                'request_id' => $requestId,
                'notes' => $data['notes'] ?? null,
            ]);

            // Resolve any active alert for this journey stage if applicable
            $this->alertService->resolveAlertForEvent($journey, $event);

            // Audit log
            AuditLog::log(
                action: 'scan_event',
                auditable: $event,
                newValues: ['activity' => $activity, 'journey_id' => $journey->id],
                reason: 'Petugas mencatat aktivitas santri via QR'
            );

            return [
                'status' => 'success',
                'event' => $event,
                'journey' => $journey,
                'message' => 'Aktivitas berhasil dicatat.',
            ];
        });
    }

    /**
     * Record a manual event (with reason and backdate check)
     */
    public function recordManualEvent(array $data, User $actor): array
    {
        $santri = Santri::findOrFail($data['santri_id']);
        $occurredAt = Carbon::parse($data['occurred_at'], 'Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');

        if ($occurredAt->greaterThan($now)) {
            throw ValidationException::withMessages([
                'occurred_at' => ['Waktu kejadian tidak boleh di masa depan.'],
            ]);
        }

        // If backdate > 24 hours, must submit as correction request
        if ($now->diffInHours($occurredAt) > 24) {
            throw ValidationException::withMessages([
                'occurred_at' => ['Pencatatan lebih dari 24 jam yang lalu harus diajukan melalui permohonan koreksi kepada pengurus.'],
            ]);
        }

        $requestId = $data['request_id'] ?? ('MANUAL-'.uniqid('', true));

        return DB::transaction(function () use ($data, $actor, $santri, $occurredAt, $now, $requestId) {
            $assignment = OfficerAssignment::with('location')->findOrFail($data['assignment_id']);
            $activity = $data['activity'];

            $journey = Journey::where('santri_id', $santri->id)
                ->whereNotIn('status', ['selesai', 'rekonsiliasi'])
                ->lockForUpdate()
                ->latest('started_at')
                ->first();

            if ($activity === 'keluar_pondok') {
                if ($journey) {
                    throw new Exception('Santri masih memiliki perjalanan aktif yang belum selesai.');
                }

                $journey = Journey::create([
                    'santri_id' => $santri->id,
                    'type' => 'reguler',
                    'operational_date' => $occurredAt->toDateString(),
                    'status' => 'menuju_kampus',
                    'started_at' => $occurredAt,
                    'organization_snapshot' => $santri->getOrganizationSnapshot(),
                ]);
            } else {
                if (! $journey) {
                    // Incomplete journey reconciliation or auto-create incomplete
                    $journey = Journey::create([
                        'santri_id' => $santri->id,
                        'type' => 'reguler',
                        'operational_date' => $occurredAt->toDateString(),
                        'status' => ($activity === 'kembali_pondok') ? 'selesai' : 'menuju_pondok',
                        'started_at' => $occurredAt,
                        'completed_at' => ($activity === 'kembali_pondok') ? $occurredAt : null,
                        'is_incomplete' => true,
                        'reconciliation_notes' => 'Dibuat melalui pencatatan manual tanpa tahap awal',
                        'organization_snapshot' => $santri->getOrganizationSnapshot(),
                    ]);
                } else {
                    if ($activity === 'kembali_pondok') {
                        $journey->status = 'selesai';
                        $journey->completed_at = $occurredAt;
                    } elseif ($activity === 'tiba_kampus') {
                        $journey->status = 'di_kampus';
                    } elseif ($activity === 'keluar_kampus') {
                        $journey->status = 'menuju_pondok';
                    }
                    $journey->save();
                }
            }

            $event = JourneyEvent::create([
                'journey_id' => $journey->id,
                'activity' => $activity,
                'occurred_at' => $occurredAt,
                'recorded_at' => $now,
                'actor_id' => $actor->id,
                'location_id' => $assignment->location_id,
                'method' => 'manual',
                'request_id' => $requestId,
                'notes' => $data['reason'],
            ]);

            $this->alertService->resolveAlertForEvent($journey, $event);

            AuditLog::log(
                action: 'manual_event',
                auditable: $event,
                reason: $data['reason']
            );

            return [
                'status' => 'success',
                'event' => $event,
                'journey' => $journey,
                'message' => 'Aktivitas manual berhasil dicatat.',
            ];
        });
    }

    /**
     * Submit a correction request
     */
    public function requestCorrection(int $targetEventId, array $proposedData, string $reason, User $actor): CorrectionRequest
    {
        $targetEvent = JourneyEvent::findOrFail($targetEventId);

        return CorrectionRequest::create([
            'target_event_id' => $targetEvent->id,
            'proposed_data' => $proposedData,
            'reason' => $reason,
            'status' => 'pending',
            'requested_by' => $actor->id,
        ]);
    }

    /**
     * Approve a correction request (creates non-destructive replacement event & audits)
     */
    public function approveCorrection(CorrectionRequest $request, User $reviewer, string $reason): void
    {
        DB::transaction(function () use ($request, $reviewer, $reason) {
            $event = $request->targetEvent;
            $oldData = $event->toArray();
            $proposed = $request->proposed_data;

            if (isset($proposed['occurred_at'])) {
                $event->occurred_at = Carbon::parse($proposed['occurred_at']);
            }
            if (isset($proposed['activity'])) {
                $event->activity = $proposed['activity'];
            }
            if (isset($proposed['notes'])) {
                $event->notes = $proposed['notes']." (Koreksi disetujui: {$reason})";
            }
            $event->save();

            $request->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            AuditLog::log(
                action: 'correction_approved',
                auditable: $request,
                oldValues: $oldData,
                newValues: $event->toArray(),
                reason: $reason
            );
        });
    }
}
