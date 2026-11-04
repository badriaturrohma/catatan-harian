<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\RuleVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuleController extends Controller
{
    public function index(): View
    {
        $currentGlobalRules = RuleVersion::getActiveParameters();
        $rulesHistory = RuleVersion::with(['creator', 'previousVersion'])
            ->orderBy('effective_at', 'desc')
            ->paginate(10);
        $campuses = Campus::where('is_active', true)->get();

        return view('admin.rules.index', compact('currentGlobalRules', 'rulesHistory', 'campuses'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:global,campus'],
            'scope_id' => ['nullable', 'exists:campuses,id'],
            'threshold_yellow_arrive_campus' => ['required', 'integer', 'min:5'],
            'threshold_red_arrive_campus' => ['required', 'integer', 'gt:threshold_yellow_arrive_campus'],
            'regular_return_curfew' => ['required', 'date_format:H:i'],
            'curfew_late_yellow' => ['required', 'integer', 'min:5'],
            'curfew_late_red' => ['required', 'integer', 'gt:curfew_late_yellow'],
            'permit_late_yellow' => ['required', 'integer', 'min:5'],
            'permit_late_red' => ['required', 'integer', 'gt:permit_late_yellow'],
            'change_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $latestRule = RuleVersion::where('scope', $validated['scope'])
            ->where('is_active', true)
            ->latest('effective_at')
            ->first();

        $parameters = [
            'threshold_yellow_arrive_campus' => (int) $validated['threshold_yellow_arrive_campus'],
            'threshold_red_arrive_campus' => (int) $validated['threshold_red_arrive_campus'],
            'regular_return_curfew' => $validated['regular_return_curfew'],
            'curfew_late_yellow' => (int) $validated['curfew_late_yellow'],
            'curfew_late_red' => (int) $validated['curfew_late_red'],
            'permit_late_yellow' => (int) $validated['permit_late_yellow'],
            'permit_late_red' => (int) $validated['permit_late_red'],
        ];

        $newVersion = RuleVersion::create([
            'scope' => $validated['scope'],
            'scope_id' => $validated['scope_id'] ?? null,
            'parameters' => $parameters,
            'effective_at' => now(),
            'created_by' => $request->user()->id,
            'previous_version_id' => $latestRule?->id,
            'change_reason' => $validated['change_reason'],
            'is_active' => true,
        ]);

        AuditLog::log(
            'rule_version_created',
            $newVersion,
            oldValues: $latestRule?->parameters,
            newValues: $parameters,
            reason: $validated['change_reason']
        );

        return back()->with('success', 'Versi aturan baru berhasil disimpan dan diberlakukan.');
    }
}
