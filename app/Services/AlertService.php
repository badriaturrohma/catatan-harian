<?php

namespace App\Services;

use App\Models\AlertEpisode;
use App\Models\AlertEvaluation;
use App\Models\Followup;
use App\Models\Journey;
use App\Models\JourneyEvent;
use App\Models\RuleVersion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AlertService
{
    /**
     * Run evaluation on all active (open) journeys
     */
    public function evaluateActiveJourneys(): Collection
    {
        $now = Carbon::now('Asia/Jakarta');
        $openJourneys = Journey::with(['santri.campus', 'events', 'permit', 'activeAlertEpisode'])
            ->whereNotIn('status', ['selesai', 'rekonsiliasi'])
            ->get();

        $generatedEpisodes = collect();

        foreach ($openJourneys as $journey) {
            $episode = $this->evaluateJourney($journey, $now);
            if ($episode) {
                $generatedEpisodes->push($episode);
            }
        }

        return $generatedEpisodes;
    }

    /**
     * Evaluate a single journey and create/update AlertEpisode accordingly
     */
    public function evaluateJourney(Journey $journey, ?Carbon $now = null): ?AlertEpisode
    {
        $now = $now ?? Carbon::now('Asia/Jakarta');
        $rules = RuleVersion::getActiveParameters($journey->santri->campus_id ?? null);

        // 1. Check Condition 1: Belum Tiba di Kampus (for regular journey or campus permit)
        if ($journey->status === 'menuju_kampus') {
            // Check if permit disables campus arrival alert
            $isNonCampusPermit = ($journey->type === 'izin' && $journey->permit && ! $journey->permit->isKampus());
            if (! $isNonCampusPermit) {
                $keluarEvent = $journey->events->firstWhere('activity', 'keluar_pondok');
                if ($keluarEvent) {
                    $minutesElapsed = $keluarEvent->occurred_at->diffInMinutes($now);
                    $yellowThreshold = (int) ($rules['threshold_yellow_arrive_campus'] ?? 15);
                    $redThreshold = (int) ($rules['threshold_red_arrive_campus'] ?? 30);

                    if ($minutesElapsed >= $yellowThreshold) {
                        $level = ($minutesElapsed >= $redThreshold) ? 'merah' : 'kuning';
                        $thresholdAt = $keluarEvent->occurred_at->copy()->addMinutes($yellowThreshold);

                        return $this->recordOrUpdateEpisode($journey, 'belum_tiba_kampus', $level, $thresholdAt, $now);
                    }
                }
            }
        }

        // 2. Check Condition 2: Belum Kembali (Reguler)
        if ($journey->type === 'reguler' && in_array($journey->status, ['di_kampus', 'menuju_pondok'])) {
            $curfewStr = $rules['regular_return_curfew'] ?? '16:00';
            $curfewTime = Carbon::parse($journey->operational_date->format('Y-m-d').' '.$curfewStr, 'Asia/Jakarta');

            if ($now->greaterThan($curfewTime)) {
                $minutesPastCurfew = $curfewTime->diffInMinutes($now);
                $yellowOffset = (int) ($rules['curfew_late_yellow'] ?? 15);
                $redOffset = (int) ($rules['curfew_late_red'] ?? 30);

                $level = ($minutesPastCurfew >= $redOffset) ? 'merah' : (($minutesPastCurfew >= $yellowOffset) ? 'kuning' : 'kuning');

                return $this->recordOrUpdateEpisode($journey, 'belum_kembali_reguler', $level, $curfewTime, $now);
            }
        }

        // 3. Check Condition 3: Belum Kembali (Izin)
        if ($journey->type === 'izin' && $journey->permit) {
            $deadline = $journey->permit->deadline;

            if ($now->greaterThan($deadline)) {
                $minutesPastDeadline = $deadline->diffInMinutes($now);
                $yellowOffset = (int) ($rules['permit_late_yellow'] ?? 15);
                $redOffset = (int) ($rules['permit_late_red'] ?? 30);

                $level = ($minutesPastDeadline >= $redOffset) ? 'merah' : 'kuning';

                return $this->recordOrUpdateEpisode($journey, 'belum_kembali_izin', $level, $deadline, $now);
            }
        }

        return null;
    }

    /**
     * Helper to create or upgrade an episode
     */
    protected function recordOrUpdateEpisode(
        Journey $journey,
        string $type,
        string $level,
        Carbon $thresholdAt,
        Carbon $now
    ): AlertEpisode {
        $episode = AlertEpisode::where('journey_id', $journey->id)
            ->where('type', $type)
            ->whereNull('resolved_at')
            ->first();

        if (! $episode) {
            $episode = AlertEpisode::create([
                'journey_id' => $journey->id,
                'type' => $type,
                'highest_level' => $level,
                'threshold_at' => $thresholdAt,
                'detected_at' => $now,
            ]);

            AlertEvaluation::create([
                'episode_id' => $episode->id,
                'evaluated_at' => $now,
                'state' => $level,
                'change_reason' => 'Peringatan baru terdeteksi oleh sistem',
            ]);
        } else {
            // If already exists and upgraded to red
            if ($episode->highest_level === 'kuning' && $level === 'merah') {
                $episode->highest_level = 'merah';
                $episode->save();

                AlertEvaluation::create([
                    'episode_id' => $episode->id,
                    'evaluated_at' => $now,
                    'state' => 'merah',
                    'change_reason' => 'Status peringatan meningkat menjadi merah',
                ]);
            }
        }

        return $episode;
    }

    /**
     * Resolve active alert when an arriving or returning event is recorded
     */
    public function resolveAlertForEvent(Journey $journey, JourneyEvent $event): void
    {
        $episodes = AlertEpisode::where('journey_id', $journey->id)
            ->whereNull('resolved_at')
            ->get();

        foreach ($episodes as $episode) {
            $shouldResolve = false;

            if ($episode->type === 'belum_tiba_kampus' && $event->activity === 'tiba_kampus') {
                $shouldResolve = true;
            } elseif (in_array($episode->type, ['belum_kembali_reguler', 'belum_kembali_izin']) && $event->activity === 'kembali_pondok') {
                $shouldResolve = true;
            }

            if ($shouldResolve) {
                $resolutionKind = match ($event->method) {
                    'qr_scan' => 'event_tercatat',
                    'manual' => 'manual',
                    'reconciliation' => 'rekonsiliasi',
                    default => 'event_tercatat',
                };

                $episode->update([
                    'resolved_at' => $event->occurred_at,
                    'resolution_kind' => $resolutionKind,
                ]);

                AlertEvaluation::create([
                    'episode_id' => $episode->id,
                    'evaluated_at' => now(),
                    'state' => 'resolved',
                    'change_reason' => "Terselesaikan oleh pencatatan {$event->getActivityLabel()} via {$event->method}",
                ]);
            }
        }
    }

    /**
     * Add followup note to an alert episode
     */
    public function addFollowup(int $episodeId, string $notes, User $actor): Followup
    {
        $episode = AlertEpisode::findOrFail($episodeId);

        return Followup::create([
            'episode_id' => $episode->id,
            'actor_id' => $actor->id,
            'notes' => $notes,
        ]);
    }
}
