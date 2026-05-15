<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;

class PerformanceReportController extends Controller
{
    public function show(CourseClass $courseClass)
    {
        $courseClass->load(['course', 'instructor']);

        return view('reports.performance', [
            'courseClass' => $courseClass,
            'courseClassId' => $courseClass->id,
        ]);
    }
}
