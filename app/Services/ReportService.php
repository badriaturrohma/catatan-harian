<?php

namespace App\Services;

use App\Models\Journey;
use App\Models\Santri;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    /**
     * Build daily report data for a specific date and filters
     */
    public function getDailyReport(string $date, array $filters = [], ?User $viewer = null): array
    {
        $query = Journey::with(['santri.campus', 'santri.program', 'santri.room', 'events.actor', 'events.location', 'permit', 'alertEpisodes'])
            ->whereDate('operational_date', $date);

        $this->applyFilters($query, $filters, $viewer);

        $journeys = $query->orderBy('started_at', 'asc')->get();

        $totalJourneys = $journeys->count();
        $uniqueSantriCount = $journeys->pluck('santri_id')->unique()->count();
        $completeCount = $journeys->where('status', 'selesai')->where('is_incomplete', false)->count();
        $incompleteCount = $journeys->where('is_incomplete', true)->count();
        $openCount = $journeys->whereNotIn('status', ['selesai', 'rekonsiliasi'])->count();

        $manualCount = $journeys->filter(function ($j) {
            return $j->events->contains('method', 'manual');
        })->count();

        $alertCount = $journeys->filter(function ($j) {
            return $j->alertEpisodes->isNotEmpty();
        })->count();

        $summary = [
            'date' => $date,
            'total_journeys' => $totalJourneys,
            'unique_santri' => $uniqueSantriCount,
            'complete' => $completeCount,
            'incomplete' => $incompleteCount,
            'open' => $openCount,
            'manual_count' => $manualCount,
            'alert_count' => $alertCount,
        ];

        // Format journey rows for display and export
        $rows = $journeys->map(function ($j) {
            $keluarPondok = $j->events->firstWhere('activity', 'keluar_pondok');
            $tibaKampus = $j->events->firstWhere('activity', 'tiba_kampus');
            $keluarKampus = $j->events->firstWhere('activity', 'keluar_kampus');
            $kembaliPondok = $j->events->firstWhere('activity', 'kembali_pondok');

            $durationMinutes = null;
            if ($keluarPondok && $kembaliPondok) {
                $durationMinutes = $keluarPondok->occurred_at->diffInMinutes($kembaliPondok->occurred_at);
            }

            $highestAlert = $j->alertEpisodes->sortByDesc('highest_level')->first();

            return [
                'journey_id' => $j->id,
                'niup' => $j->santri->niup ?? ($j->organization_snapshot['niup'] ?? '-'),
                'santri_name' => $j->santri->name ?? ($j->organization_snapshot['name'] ?? '-'),
                'campus' => $j->santri->campus->name ?? ($j->organization_snapshot['campus_name'] ?? '-'),
                'program' => $j->santri->program->name ?? ($j->organization_snapshot['program_name'] ?? '-'),
                'room' => $j->santri->room->name ?? ($j->organization_snapshot['room_name'] ?? '-'),
                'type' => $j->type,
                'status' => $j->status,
                'is_incomplete' => $j->is_incomplete,
                'keluar_pondok_at' => $keluarPondok ? $keluarPondok->occurred_at->format('H:i') : 'Belum tercatat',
                'tiba_kampus_at' => $tibaKampus ? $tibaKampus->occurred_at->format('H:i') : ($j->type === 'izin' && $j->permit && ! $j->permit->isKampus() ? 'N/A (Non-Kampus)' : 'Belum tercatat'),
                'keluar_kampus_at' => $keluarKampus ? $keluarKampus->occurred_at->format('H:i') : ($j->type === 'izin' && $j->permit && ! $j->permit->isKampus() ? 'N/A (Non-Kampus)' : 'Belum tercatat'),
                'kembali_pondok_at' => $kembaliPondok ? $kembaliPondok->occurred_at->format('H:i') : 'Belum tercatat',
                'duration' => $durationMinutes !== null ? "{$durationMinutes} menit" : '-',
                'highest_alert' => $highestAlert ? $highestAlert->highest_level : null,
                'has_manual' => $j->events->contains('method', 'manual'),
                'permit_info' => $j->permit ? "{$j->permit->destination_name} (Batas: {$j->permit->deadline->format('H:i')})" : null,
            ];
        });

        return [
            'summary' => $summary,
            'rows' => $rows,
            'date' => $date,
        ];
    }

    /**
     * Build monthly report data
     */
    public function getMonthlyReport(string $yearMonth, array $filters = [], ?User $viewer = null): array
    {
        $carbonDate = Carbon::createFromFormat('Y-m', $yearMonth);
        $startOfMonth = $carbonDate->copy()->startOfMonth()->toDateString();
        $endOfMonth = $carbonDate->copy()->endOfMonth()->toDateString();

        $query = Journey::with(['santri.campus'])
            ->whereBetween('operational_date', [$startOfMonth, $endOfMonth]);

        $this->applyFilters($query, $filters, $viewer);

        $journeys = $query->get();

        $totalJourneys = $journeys->count();
        // Unique monthly santri count must be distinct across the entire month
        $uniqueSantriCount = $journeys->pluck('santri_id')->unique()->count();

        // Daily breakdown
        $daysInMonth = $carbonDate->daysInMonth;
        $dailyBreakdown = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDateStr = $carbonDate->copy()->day($day)->toDateString();
            $dayJourneys = $journeys->where('operational_date', Carbon::parse($currentDateStr));

            $dailyBreakdown[] = [
                'date' => $currentDateStr,
                'day' => $day,
                'total_journeys' => $dayJourneys->count(),
                'unique_santri' => $dayJourneys->pluck('santri_id')->unique()->count(),
                'complete' => $dayJourneys->where('status', 'selesai')->where('is_incomplete', false)->count(),
                'incomplete' => $dayJourneys->where('is_incomplete', true)->count(),
            ];
        }

        return [
            'year_month' => $yearMonth,
            'month_name' => $carbonDate->translatedFormat('F Y'),
            'total_journeys' => $totalJourneys,
            'unique_santri' => $uniqueSantriCount,
            'daily_breakdown' => $dailyBreakdown,
        ];
    }

    /**
     * Export daily report as CSV with formula injection protection
     */
    public function exportDailyCsv(string $date, array $filters = [], ?User $viewer = null): StreamedResponse
    {
        $report = $this->getDailyReport($date, $filters, $viewer);

        $filename = "rekap_harian_{$date}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return new StreamedResponse(function () use ($report) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($handle, [
                'ID Perjalanan',
                'NIUP',
                'Nama Santri',
                'Kampus',
                'Program Studi',
                'Kamar',
                'Jenis',
                'Status',
                'Keluar Pondok',
                'Tiba Kampus',
                'Keluar Kampus',
                'Kembali Pondok',
                'Durasi',
                'Status Alert',
                'Ada Manual',
                'Keterangan Izin',
            ]);

            foreach ($report['rows'] as $row) {
                fputcsv($handle, [
                    $this->sanitizeCsvField($row['journey_id']),
                    $this->sanitizeCsvField($row['niup']),
                    $this->sanitizeCsvField($row['santri_name']),
                    $this->sanitizeCsvField($row['campus']),
                    $this->sanitizeCsvField($row['program']),
                    $this->sanitizeCsvField($row['room']),
                    $this->sanitizeCsvField($row['type']),
                    $this->sanitizeCsvField($row['status']),
                    $this->sanitizeCsvField($row['keluar_pondok_at']),
                    $this->sanitizeCsvField($row['tiba_kampus_at']),
                    $this->sanitizeCsvField($row['keluar_kampus_at']),
                    $this->sanitizeCsvField($row['kembali_pondok_at']),
                    $this->sanitizeCsvField($row['duration']),
                    $this->sanitizeCsvField($row['highest_alert'] ?? '-'),
                    $row['has_manual'] ? 'Ya' : 'Tidak',
                    $this->sanitizeCsvField($row['permit_info'] ?? '-'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Protect against CSV Formula Injection
     */
    protected function sanitizeCsvField(mixed $value): string
    {
        $str = (string) $value;
        if (in_array(substr($str, 0, 1), ['=', '+', '-', '@', "\t", "\r"])) {
            return "'".$str;
        }

        return $str;
    }

    /**
     * Apply standard search & privacy filters
     */
    protected function applyFilters(Builder $query, array $filters, ?User $viewer = null): void
    {
        // Role privacy: if viewer is a wali, restrict to their verified children
        if ($viewer && $viewer->isWali()) {
            $verifiedChildIds = $viewer->verifiedChildren()->pluck('santris.id')->toArray();
            $query->whereIn('santri_id', $verifiedChildIds);
        }

        if (! empty($filters['campus_id'])) {
            $query->whereHas('santri', function ($q) use ($filters) {
                $q->where('campus_id', $filters['campus_id']);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['santri_id'])) {
            $query->where('santri_id', $filters['santri_id']);
        }
    }
}
