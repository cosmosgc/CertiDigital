<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Services\PerformanceReportService;
use Illuminate\Http\Response;

class PerformanceReportController extends Controller
{
    public function __construct(private readonly PerformanceReportService $reportService) {}

    public function show(CourseClass $courseClass)
    {
        $report = $this->reportService->build($courseClass);

        return response()->json($report, Response::HTTP_OK);
    }
}
