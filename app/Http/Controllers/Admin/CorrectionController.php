<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Services\JourneyService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorrectionController extends Controller
{
    public function __construct(
        protected JourneyService $journeyService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $query = CorrectionRequest::with(['targetEvent.journey.santri', 'requester', 'reviewer']);

        if ($status) {
            $query->where('status', $status);
        }

        $corrections = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.corrections.index', compact('corrections', 'status'));
    }

    public function approve(Request $request, CorrectionRequest $correction): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        try {
            $this->journeyService->approveCorrection($correction, $request->user(), $validated['reason']);

            return back()->with('success', 'Permohonan koreksi berhasil disetujui.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, CorrectionRequest $correction): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $correction->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Permohonan koreksi ditolak.');
    }
}
