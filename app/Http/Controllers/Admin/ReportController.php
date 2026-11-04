<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function daily(Request $request): View
    {
        $date = $request->query('date', Carbon::today('Asia/Jakarta')->toDateString());
        $campusId = $request->query('campus_id');
        $status = $request->query('status');

        $filters = array_filter([
            'campus_id' => $campusId,
            'status' => $status,
        ]);

        $report = $this->reportService->getDailyReport($date, $filters);
        $campuses = Campus::where('is_active', true)->get();

        return view('admin.reports.daily', compact('report', 'date', 'campuses', 'campusId', 'status'));
    }

    public function monthly(Request $request): View
    {
        $yearMonth = $request->query('month', Carbon::today('Asia/Jakarta')->format('Y-m'));
        $campusId = $request->query('campus_id');

        $filters = array_filter([
            'campus_id' => $campusId,
        ]);

        $report = $this->reportService->getMonthlyReport($yearMonth, $filters);
        $campuses = Campus::where('is_active', true)->get();

        return view('admin.reports.monthly', compact('report', 'yearMonth', 'campuses', 'campusId'));
    }

    public function exportDailyCsv(Request $request): StreamedResponse
    {
        $date = $request->query('date', Carbon::today('Asia/Jakarta')->toDateString());
        $campusId = $request->query('campus_id');
        $status = $request->query('status');

        $filters = array_filter([
            'campus_id' => $campusId,
            'status' => $status,
        ]);

        return $this->reportService->exportDailyCsv($date, $filters, $request->user());
    }

    public function printDaily(Request $request): View
    {
        $date = $request->query('date', Carbon::today('Asia/Jakarta')->toDateString());
        $campusId = $request->query('campus_id');
        $status = $request->query('status');

        $filters = array_filter([
            'campus_id' => $campusId,
            'status' => $status,
        ]);

        $report = $this->reportService->getDailyReport($date, $filters);

        return view('admin.reports.print_daily', compact('report', 'date'));
    }
}
