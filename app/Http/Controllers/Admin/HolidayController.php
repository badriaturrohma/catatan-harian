<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\HolidayPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        $holidays = HolidayPeriod::with('campus')->orderBy('start_date', 'desc')->paginate(15);
        $campuses = Campus::where('is_active', true)->get();

        return view('admin.holidays.index', compact('holidays', 'campuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'departure_cutoff_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $holiday = HolidayPeriod::create($validated);
        AuditLog::log('holiday_created', $holiday, reason: 'Pengaturan periode libur kampus');

        return back()->with('success', "Periode libur {$holiday->name} berhasil ditambahkan.");
    }
}
