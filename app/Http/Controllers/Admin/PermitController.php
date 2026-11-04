<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permit;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermitController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $query = Permit::with(['santri.campus', 'approver']);

        if ($status) {
            $query->where('status', $status);
        }

        $permits = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.permits.index', compact('permits', 'status'));
    }

    public function create(): View
    {
        $santris = Santri::where('is_active', true)->orderBy('name')->get();

        return view('admin.permits.create', compact('santris'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'destination_type' => ['required', 'in:kampus,non_kampus'],
            'destination_name' => ['required', 'string', 'max:150'],
            'start_time' => ['required', 'date'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:24'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $startTime = Carbon::parse($validated['start_time']);
        $deadline = $startTime->copy()->addHours((int) $validated['duration_hours']);

        $permit = Permit::create([
            'santri_id' => $validated['santri_id'],
            'destination_type' => $validated['destination_type'],
            'destination_name' => $validated['destination_name'],
            'start_time' => $startTime,
            'duration_hours' => $validated['duration_hours'],
            'deadline' => $deadline,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        AuditLog::log(
            action: 'permit_created',
            auditable: $permit,
            reason: 'Pengajuan izin keluar santri'
        );

        return redirect()->route('admin.permits.index')->with('success', 'Pengajuan izin berhasil dibuat.');
    }

    public function approve(Request $request, Permit $permit): RedirectResponse
    {
        $permit->update([
            'status' => 'approved',
            'approver_id' => $request->user()->id,
            'approved_at' => now(),
        ]);

        AuditLog::log(
            action: 'permit_approved',
            auditable: $permit,
            reason: 'Pengurus menyetujui izin keluar'
        );

        return back()->with('success', "Izin untuk {$permit->santri->name} telah disetujui (Batas: {$permit->deadline->format('d/m/Y H:i')}).");
    }

    public function reject(Request $request, Permit $permit): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $permit->update([
            'status' => 'rejected',
            'approver_id' => $request->user()->id,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        AuditLog::log(
            action: 'permit_rejected',
            auditable: $permit,
            reason: $validated['rejection_reason']
        );

        return back()->with('success', 'Izin telah ditolak.');
    }
}
