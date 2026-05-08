<?php

namespace App\Http\Controllers\Artisan;

use App\Services\MonthlyReportService;
use Illuminate\Http\Request;

class MonthlyReportController extends ArtisanController
{
    public function __construct(
        protected MonthlyReportService $monthlyReportService
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $artisan = $this->getArtisan();

        $validated = $request->validate([
            'year' => ['sometimes', 'integer', 'min:2020', 'max:2100'],
            'month' => ['sometimes', 'integer', 'min:1', 'max:12'],
        ]);

        $year = (int) ($validated['year'] ?? now()->year);
        $month = (int) ($validated['month'] ?? now()->month);

        $report = $this->monthlyReportService->buildArtisanReport($artisan, $year, $month);

        return view('artisan.reports.monthly', [
            'report' => $report,
            'year' => $year,
            'month' => $month,
            'title' => 'Monthly report — '.$report['window']['label'],
        ]);
    }
}
