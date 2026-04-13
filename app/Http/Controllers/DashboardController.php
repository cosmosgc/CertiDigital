<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseClassAttendance;
use App\Models\CourseEnrollment;
use App\Models\ScheduleEvent;
use App\Models\Student;
use App\Models\StudentAnnotation;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard for managers.
     */
    public function index(Request $request)
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        $userCount = User::count();
        $certificateCount = Certificate::count();
        $courseCount = Course::count();
        $studentCount = Student::count();
        $classCount = CourseClass::count();
        $enrollmentCount = CourseEnrollment::count();
        $completedEnrollmentCount = CourseEnrollment::where('completed', true)->count();
        $attendanceSessionCount = CourseClassAttendance::count();
        $annotationCount = StudentAnnotation::count();

        $activeStudentsThisMonth = CourseEnrollment::where('updated_at', '>=', $startOfMonth)
            ->distinct('student_id')
            ->count('student_id');

        $certificateGrowthThisMonth = Certificate::where('created_at', '>=', $startOfMonth)->count();
        $upcomingEventCount = ScheduleEvent::whereDate('start_date', '>=', $now->toDateString())->count();

        $completionRate = $enrollmentCount > 0
            ? round(($completedEnrollmentCount / $enrollmentCount) * 100)
            : 0;

        $averageProgressHours = (float) CourseEnrollment::avg('progress_hours');
        $averageGrade = (float) CourseEnrollment::whereNotNull('grade')->avg('grade');
        $totalWorkloadHours = (float) Course::sum('workload_hours');
        $attendanceHours = (float) CourseClassAttendance::sum('duration_hours');

        $months = [];
        $certsByMonth = [];
        $enrollmentsByMonth = [];

        for ($i = 11; $i >= 0; $i--) {
            $dt = $now->copy()->subMonths($i);
            $start = $dt->copy()->startOfMonth();
            $end = $dt->copy()->endOfMonth();

            $months[] = $dt->translatedFormat('M Y');
            $certsByMonth[] = Certificate::whereBetween('created_at', [$start, $end])->count();
            $enrollmentsByMonth[] = CourseEnrollment::whereBetween('created_at', [$start, $end])->count();
        }

        $enrollmentStatus = [
            'completed' => $completedEnrollmentCount,
            'inProgress' => CourseEnrollment::where('completed', false)->count(),
            'withoutClass' => CourseEnrollment::whereNull('course_class_id')->count(),
        ];

        $recentCertificates = Certificate::with(['student', 'course'])
            ->latest()
            ->limit(6)
            ->get();

        $upcomingEvents = ScheduleEvent::with(['courseClass.course'])
            ->whereDate('start_date', '>=', $now->toDateString())
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        $recentAnnotations = StudentAnnotation::with(['student', 'courseClass.course'])
            ->latest('annotation_date')
            ->limit(5)
            ->get();

        $topCourses = Course::withCount([
                'classes',
                'enrollments',
                'certificates',
                'enrollments as completed_enrollments_count' => function ($query) {
                    $query->where('completed', true);
                },
            ])
            ->orderByDesc('enrollments_count')
            ->limit(5)
            ->get();

        $classOverview = CourseClass::with(['course', 'instructor'])
            ->withCount([
                'enrollments',
                'attendances',
                'studentAnnotations',
                'enrollments as completed_enrollments_count' => function ($query) {
                    $query->where('completed', true);
                },
            ])
            ->orderByDesc('enrollments_count')
            ->limit(4)
            ->get()
            ->map(function (CourseClass $courseClass) {
                $enrolled = (int) $courseClass->enrollments_count;
                $completed = (int) $courseClass->completed_enrollments_count;

                $courseClass->completion_rate = $enrolled > 0
                    ? (int) round(($completed / $enrolled) * 100)
                    : 0;

                return $courseClass;
            });

        return view('dashboard', compact(
            'annotationCount',
            'attendanceHours',
            'attendanceSessionCount',
            'averageGrade',
            'averageProgressHours',
            'certificateCount',
            'certificateGrowthThisMonth',
            'classCount',
            'classOverview',
            'completedEnrollmentCount',
            'completionRate',
            'courseCount',
            'certsByMonth',
            'enrollmentCount',
            'enrollmentStatus',
            'enrollmentsByMonth',
            'months',
            'recentAnnotations',
            'recentCertificates',
            'activeStudentsThisMonth',
            'studentCount',
            'topCourses',
            'totalWorkloadHours',
            'upcomingEventCount',
            'upcomingEvents',
            'userCount'
        ));
    }
}
