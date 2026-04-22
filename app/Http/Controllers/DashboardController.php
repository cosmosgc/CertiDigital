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
use Illuminate\Support\Carbon;
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

    public function liveClasses(Request $request)
    {
        $now = now();
        $weekStart = $now->copy()->startOfWeek();
        $weekDays = collect(range(0, 6))->map(fn (int $day) => $weekStart->copy()->addDays($day));

        $weeklyClassEvents = ScheduleEvent::with([
                'courseClass.course',
                'courseClass.instructor',
                'courseClass.attendances',
            ])
            ->where('event_type', 'weekly_class')
            ->whereNotNull('course_class_id')
            ->orderBy('start_time')
            ->get();

        $plannerEvents = $weeklyClassEvents
            ->flatMap(function (ScheduleEvent $event) use ($weekDays) {
                return $weekDays
                    ->filter(fn (Carbon $date) => $this->scheduleEventOccursOnDate($event, $date))
                    ->map(fn (Carbon $date) => [
                        'event' => $event,
                        'date' => $date->copy(),
                    ]);
            })
            ->sortBy(fn (array $item) => $item['date']->format('Y-m-d') . ' ' . $this->eventTimeSortValue($item['event']))
            ->values();

        $liveClasses = $weeklyClassEvents
            ->filter(fn (ScheduleEvent $event) => $this->scheduleEventIsLiveNow($event, $now))
            ->values();

        $timeSlots = $plannerEvents
            ->map(fn (array $item) => $this->eventTimeLabel($item['event']))
            ->unique()
            ->sort()
            ->values();
        $weekHeaders = $weekDays
            ->values()
            ->map(fn (Carbon $date, int $index) => [
                'weekday' => [__('Seg'), __('Ter'), __('Qua'), __('Qui'), __('Sex'), __('Sáb'), __('Dom')][$index],
                'date' => $date->format('d/m'),
            ]);
        $liveClassCards = $liveClasses
            ->map(fn (ScheduleEvent $event) => $this->eventCardData($event))
            ->values();
        $plannerRows = $timeSlots
            ->map(function (string $slot) use ($plannerEvents, $weekDays) {
                return [
                    'slot' => $slot,
                    'cells' => $weekDays->values()->map(function (Carbon $date) use ($plannerEvents, $slot) {
                        return [
                            'events' => $plannerEvents
                                ->filter(fn (array $item) => $item['date']->isSameDay($date) && $this->eventTimeLabel($item['event']) === $slot)
                                ->map(fn (array $item) => $this->plannerEventData($item['event']))
                                ->values(),
                        ];
                    }),
                ];
            })
            ->values();

        return view('dashboard-live-classes', compact(
            'liveClassCards',
            'liveClasses',
            'now',
            'plannerEvents',
            'plannerRows',
            'timeSlots',
            'weekHeaders',
            'weekDays'
        ));
    }

    private function eventCardData(ScheduleEvent $event): array
    {
        $courseClass = $event->courseClass;
        $meta = $this->attendanceMeta($courseClass);

        return [
            'time' => $this->eventTimeLabel($event),
            'title' => $courseClass?->name ?? $event->title,
            'course' => $courseClass?->course?->title ?? __('Curso não definido'),
            'instructor' => $courseClass?->instructor?->full_name,
            'location' => $event->location,
            'attendance' => $meta,
            'class_url' => $courseClass ? route('course-classes.show', $courseClass) : null,
        ];
    }

    private function plannerEventData(ScheduleEvent $event): array
    {
        $courseClass = $event->courseClass;

        return [
            'title' => $courseClass?->name ?? $event->title,
            'course' => $courseClass?->course?->title,
            'attendance' => $this->attendanceMeta($courseClass),
        ];
    }

    private function attendanceMeta(?CourseClass $courseClass): array
    {
        $attendance = $courseClass?->attendances
            ?->sortByDesc(fn (CourseClassAttendance $attendance) => ($attendance->attendance_date?->format('Y-m-d') ?? '') . '-' . str_pad((string) $attendance->id, 10, '0', STR_PAD_LEFT))
            ->first();

        if (! $attendance) {
            return [
                'label' => __('Sem presença'),
                'tag' => __('Sem sessão'),
                'classes' => 'border-slate-200 bg-slate-50 text-slate-600',
                'url' => null,
            ];
        }

        $date = $attendance->attendance_date;
        $isToday = $date?->isToday();
        $isOlder = $date?->lt(now()->startOfDay());

        return [
            'label' => $date?->format('d/m/Y') ?? __('Sem data'),
            'tag' => $isToday ? __('Hoje') : ($isOlder ? __('Antiga') : __('Futura')),
            'classes' => $isToday
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : ($isOlder ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-sky-200 bg-sky-50 text-sky-700'),
            'url' => route('course-class-attendances.show', [
                'courseClass' => $courseClass,
                'courseClassAttendance' => $attendance,
                'guest' => 1,
            ]),
        ];
    }

    private function scheduleEventOccursOnDate(ScheduleEvent $event, Carbon $date): bool
    {
        if (! $event->start_date) {
            return false;
        }

        $dateOnly = $date->toDateString();
        $startDate = $event->start_date->toDateString();
        $endDate = $event->end_date?->toDateString();

        if ($event->is_recurring_weekly) {
            $recurrenceEndDate = $endDate && $endDate !== $startDate
                ? $endDate
                : Carbon::parse($startDate)->endOfYear()->toDateString();
            $weekday = $event->weekday ?? $event->start_date->dayOfWeek;

            return $dateOnly >= $startDate
                && $dateOnly <= $recurrenceEndDate
                && $weekday === $date->dayOfWeek;
        }

        return $dateOnly >= $startDate && (! $endDate || $dateOnly <= $endDate);
    }

    private function scheduleEventIsLiveNow(ScheduleEvent $event, Carbon $now): bool
    {
        if (! $this->scheduleEventOccursOnDate($event, $now)) {
            return false;
        }

        if ($event->is_all_day) {
            return true;
        }

        if (! $event->start_time) {
            return false;
        }

        $start = Carbon::parse($now->toDateString() . ' ' . $event->start_time);
        $end = $event->end_time
            ? Carbon::parse($now->toDateString() . ' ' . $event->end_time)
            : $start->copy()->addHours(2);

        return $now->between($start, $end, true);
    }

    private function eventTimeLabel(ScheduleEvent $event): string
    {
        if ($event->is_all_day) {
            return __('Dia inteiro');
        }

        $start = $event->start_time ? substr((string) $event->start_time, 0, 5) : '--:--';
        $end = $event->end_time ? substr((string) $event->end_time, 0, 5) : null;

        return $end ? "{$start} - {$end}" : $start;
    }

    private function eventTimeSortValue(ScheduleEvent $event): string
    {
        return $event->is_all_day ? '00:00' : ($event->start_time ? substr((string) $event->start_time, 0, 5) : '23:59');
    }
}
