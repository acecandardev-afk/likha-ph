<?php

namespace App\Http\Controllers\Admin;

use App\Services\MonthlyReportService;
use Illuminate\Http\Request;

class MonthlyReportController extends AdminController
{
    public function __construct(
        protected MonthlyReportService $monthlyReportService
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'year' => ['sometimes', 'integer', 'min:2020', 'max:2100'],
            'month' => ['sometimes', 'integer', 'min:1', 'max:12'],
        ]);

        $year = (int) ($validated['year'] ?? now()->year);
        $month = (int) ($validated['month'] ?? now()->month);

        $report = $this->monthlyReportService->buildAdminReport($year, $month);

        return view('admin.reports.monthly', [
            'report' => $report,
            'year' => $year,
            'month' => $month,
            'title' => 'Monthly report — '.$report['window']['label'],
        ]);
    }
}
