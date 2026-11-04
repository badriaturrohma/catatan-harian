<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\OfficerAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(): View
    {
        $assignments = OfficerAssignment::with(['user', 'location.campus'])
            ->orderBy('is_active', 'desc')
            ->orderBy('valid_from', 'desc')
            ->paginate(15);

        $officers = User::where('role', 'petugas')->where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();

        return view('admin.assignments.index', compact('assignments', 'officers', 'locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $assignment = OfficerAssignment::create($validated);
        AuditLog::log('assignment_created', $assignment, reason: 'Penugasan pos petugas baru');

        return back()->with('success', 'Penugasan petugas berhasil disimpan.');
    }

    public function toggle(OfficerAssignment $assignment): RedirectResponse
    {
        $assignment->update(['is_active' => ! $assignment->is_active]);

        AuditLog::log(
            'assignment_status_toggled',
            $assignment,
            newValues: ['is_active' => $assignment->is_active],
            reason: 'Perubahan status aktif penugasan'
        );

        return back()->with('success', 'Status penugasan diperbarui.');
    }
}
