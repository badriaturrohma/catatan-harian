<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Location;
use App\Models\Program;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(): View
    {
        $campuses = Campus::withCount(['programs', 'locations', 'santris'])->get();
        $programs = Program::with('campus')->get();
        $locations = Location::with('campus')->get();
        $rooms = Room::with('area.region')->get();

        return view('admin.master.index', compact('campuses', 'programs', 'locations', 'rooms'));
    }

    public function storeCampus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'unique:campuses,code', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $campus = Campus::create($validated);
        AuditLog::log('campus_created', $campus, reason: 'Tambah data kampus');

        return back()->with('success', "Kampus {$campus->name} berhasil ditambahkan.");
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:pondok,kampus'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
        ]);

        $location = Location::create($validated);
        AuditLog::log('location_created', $location, reason: 'Tambah lokasi pos tapping');

        return back()->with('success', "Pos lokasi {$location->name} berhasil ditambahkan.");
    }
}
