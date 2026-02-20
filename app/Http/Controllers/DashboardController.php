<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard for managers.
     */
    public function index(Request $request)
    {
        $userCount = User::count();
        $certificateCount = Certificate::count();
        $courseCount = Course::count();
        $studentCount = Student::count();

        $months = [];
        $certsByMonth = [];
        for ($i = 11; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $months[] = $dt->format('M Y');
            $start = $dt->copy()->startOfMonth();
            $end = $dt->copy()->endOfMonth();
            $certsByMonth[] = Certificate::whereBetween('created_at', [$start, $end])->count();
        }

        $recentCertificates = Certificate::with(['student','course'])->latest()->limit(5)->get();

        return view('dashboard', compact(
            'userCount',
            'certificateCount',
            'courseCount',
            'studentCount',
            'months',
            'certsByMonth',
            'recentCertificates'
        ));
    }
}
