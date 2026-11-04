<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlertEpisode;
use App\Models\Campus;
use App\Models\Journey;
use App\Models\JourneyEvent;
use App\Models\Santri;
use App\Services\AlertService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected AlertService $alertService
    ) {}

    public function index(Request $request): View
    {
        // Run alert evaluation on open journeys
        $this->alertService->evaluateActiveJourneys();

        $today = Carbon::today('Asia/Jakarta');
        $campusId = $request->query('campus_id');

        // Campuses for filter
        $campuses = Campus::where('is_active', true)->get();

        // Base query for open journeys
        $openJourneysQuery = Journey::with(['santri.campus', 'santri.program', 'santri.room', 'latestEvent', 'activeAlertEpisode', 'permit'])
            ->whereNotIn('status', ['selesai', 'rekonsiliasi']);

        if ($campusId) {
            $openJourneysQuery->whereHas('santri', fn ($q) => $q->where('campus_id', $campusId));
        }

        $openJourneys = $openJourneysQuery->orderBy('started_at', 'desc')->get();

        // Active alerts (unresolved)
        $activeAlertsQuery = AlertEpisode::with(['journey.santri.campus', 'journey.latestEvent', 'followups.actor'])
            ->whereNull('resolved_at');

        if ($campusId) {
            $activeAlertsQuery->whereHas('journey.santri', fn ($q) => $q->where('campus_id', $campusId));
        }

        $activeAlerts = $activeAlertsQuery->orderByRaw("FIELD(highest_level, 'merah', 'kuning')")
            ->orderBy('detected_at', 'desc')
            ->get();

        // Summary counts
        $totalSantriActive = Santri::where('is_active', true)->count();
        $totalDepartedToday = Journey::whereDate('operational_date', $today)->count();
        $totalCompletedToday = Journey::whereDate('operational_date', $today)->where('status', 'selesai')->count();
        $totalOpenJourneys = $openJourneys->count();
        $alertYellowCount = $activeAlerts->where('highest_level', 'kuning')->count();
        $alertRedCount = $activeAlerts->where('highest_level', 'merah')->count();

        // Recent activity events today
        $recentEvents = JourneyEvent::with(['journey.santri', 'location', 'actor'])
            ->whereDate('recorded_at', $today)
            ->latest('recorded_at')
            ->take(12)
            ->get();

        return view('admin.dashboard', compact(
            'campuses',
            'campusId',
            'openJourneys',
            'activeAlerts',
            'totalSantriActive',
            'totalDepartedToday',
            'totalCompletedToday',
            'totalOpenJourneys',
            'alertYellowCount',
            'alertRedCount',
            'recentEvents'
        ));
    }

    /**
     * Add followup note to an alert
     */
    public function followupAlert(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $this->alertService->addFollowup($id, $validated['notes'], $request->user());

        return back()->with('success', 'Catatan tindak lanjut berhasil ditambahkan.');
    }
}
