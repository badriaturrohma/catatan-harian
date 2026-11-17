<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\JourneyEvent;
use App\Models\OfficerAssignment;
use App\Models\Santri;
use App\Services\JourneyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function __construct(
        protected JourneyService $journeyService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $assignments = OfficerAssignment::with('location.campus')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $activeAssignment = $user->activeAssignment()->with('location.campus')->first() ?? $assignments->first();

        // If pengurus has no assignments yet, auto-assign to the first active pos location
        if (! $activeAssignment && $user->isPengurus()) {
            $firstLocation = \App\Models\Location::with('campus')->where('is_active', true)->first();
            if ($firstLocation) {
                $activeAssignment = OfficerAssignment::create([
                    'user_id' => $user->id,
                    'location_id' => $firstLocation->id,
                    'valid_from' => now()->subDay(),
                    'valid_until' => now()->addYear(),
                    'is_active' => true,
                    'notes' => 'Akses Monitoring Pengurus',
                ])->load('location.campus');

                $assignments = collect([$activeAssignment]);
            }
        }

        // Recent events recorded by this officer today
        $recentEvents = JourneyEvent::with(['journey.santri', 'location'])
            ->where('actor_id', $user->id)
            ->whereDate('recorded_at', now()->toDateString())
            ->latest('recorded_at')
            ->take(15)
            ->get();

        $activeSantris = Santri::where('is_active', true)->orderBy('name')->get(['id', 'niup', 'name']);

        return view('officer.scan', compact('assignments', 'activeAssignment', 'recentEvents', 'activeSantris'));
    }

    /**
     * Resolve QR token to verify identity and obtain allowed activities
     */
    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'assignment_id' => ['nullable', 'exists:officer_assignments,id'],
        ]);

        $assignmentId = $validated['assignment_id'] ?? null;
        if (! $assignmentId) {
            $userAssignment = $request->user()->activeAssignment()->first()
                ?? OfficerAssignment::where('user_id', $request->user()->id)->where('is_active', true)->first();

            if (! $userAssignment && $request->user()->isPengurus()) {
                $firstLoc = \App\Models\Location::where('is_active', true)->first();
                if ($firstLoc) {
                    $userAssignment = OfficerAssignment::firstOrCreate([
                        'user_id' => $request->user()->id,
                        'location_id' => $firstLoc->id,
                    ], [
                        'valid_from' => now()->subDay(),
                        'valid_until' => now()->addYear(),
                        'is_active' => true,
                        'notes' => 'Akses Monitoring Pengurus',
                    ]);
                }
            }

            $assignmentId = $userAssignment?->id;
        }

        if (! $assignmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Penugasan pos aktif tidak ditemukan untuk akun Anda.',
            ], 422);
        }

        try {
            $result = $this->journeyService->resolveQrToken(
                $validated['token'],
                (int) $assignmentId,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Commit scan tapping event
     */
    public function record(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'credential_id' => ['required', 'exists:qr_credentials,id'],
            'assignment_id' => ['required', 'exists:officer_assignments,id'],
            'activity' => ['required', 'in:keluar_pondok,tiba_kampus,keluar_kampus,kembali_pondok'],
            'request_id' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $this->journeyService->recordEvent($validated, $request->user());

            return response()->json([
                'success' => true,
                'status' => $result['status'],
                'message' => $result['message'],
                'event_id' => $result['event']->id,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Commit manual event
     */
    public function recordManual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'assignment_id' => ['required', 'exists:officer_assignments,id'],
            'activity' => ['required', 'in:keluar_pondok,tiba_kampus,keluar_kampus,kembali_pondok'],
            'occurred_at' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        try {
            $result = $this->journeyService->recordManualEvent($validated, $request->user());

            return back()->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->withErrors(['manual' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Submit correction request
     */
    public function requestCorrection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_event_id' => ['required', 'exists:journey_events,id'],
            'occurred_at' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $this->journeyService->requestCorrection(
                (int) $validated['target_event_id'],
                ['occurred_at' => $validated['occurred_at']],
                $validated['reason'],
                $request->user()
            );

            return back()->with('success', 'Permohonan koreksi berhasil diajukan kepada pengurus.');
        } catch (Exception $e) {
            return back()->withErrors(['correction' => $e->getMessage()]);
        }
    }
}
