<?php

namespace App\Services;

use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentProgressService
{
    public function refresh(CourseEnrollment $enrollment): CourseEnrollment
    {
        $enrollment->loadMissing('course');

        $progressHours = (float) DB::table('course_class_attendance_records')
            ->join('course_class_attendances', 'course_class_attendances.id', '=', 'course_class_attendance_records.course_class_attendance_id')
            ->where('course_class_attendances.course_class_id', $enrollment->course_class_id)
            ->where('course_class_attendance_records.student_id', $enrollment->student_id)
            ->sum('course_class_attendances.duration_hours');

        $workloadHours = (float) ($enrollment->course?->workload_hours ?? 0);

        $enrollment->forceFill([
            'progress_hours' => $progressHours,
            'completed' => $workloadHours > 0 ? $progressHours >= $workloadHours : false,
        ])->save();

        return $enrollment->fresh(['student', 'course', 'courseClass']);
    }
}
