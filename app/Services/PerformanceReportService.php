<?php

namespace App\Services;

use App\Models\CourseClass;
use Illuminate\Support\Collection;

class PerformanceReportService
{
    public function build(CourseClass $courseClass): array
    {
        $courseClass->loadMissing([
            'course',
            'instructor',
            'enrollments.student',
            'enrollments.trimesterGrades',
            'attendances.records.student',
        ]);

        $attendances = $courseClass->attendances->sortBy('attendance_date');

        $recordsByStudent = $this->buildRecordsByStudent($attendances);

        $studentsData = $courseClass->enrollments->map(function ($enrollment) use ($attendances, $recordsByStudent) {
            $studentId = $enrollment->student_id;
            $recordsByAttendance = $recordsByStudent[$studentId] ?? collect();

            $trimesterData = $this->buildTrimesterData($attendances, $recordsByAttendance);
            $overallFrequency = $this->computeFrequency($attendances, $recordsByAttendance);
            $trimesterGrades = $enrollment->trimesterGrades->keyBy('trimester');
            $student = $enrollment->student;

            return [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'birth_date' => $student->birth_date?->format('Y-m-d'),
                'email' => $student->email,
                'document_id' => $student->document_id,
                'period_start' => $enrollment->start_date?->format('Y-m-d'),
                'period_end' => $enrollment->end_date?->format('Y-m-d'),
                'enrollment_id' => $enrollment->id,
                'final_grade' => $enrollment->grade,
                'progress_hours' => $enrollment->progress_hours,
                'completed' => $enrollment->completed,
                'attendances' => $this->buildAttendanceMatrix($attendances, $recordsByAttendance),
                'trimester_summaries' => $trimesterData,
                'trimester_grades' => $this->buildTrimesterGradeBlocks($trimesterGrades),
                'overall_frequency' => $overallFrequency,
            ];
        });

        return [
            'class' => [
                'id' => $courseClass->id,
                'name' => $courseClass->name,
                'course' => $courseClass->course?->only(['id', 'title', 'workload_hours']),
                'instructor' => $courseClass->instructor?->only(['id', 'full_name']),
            ],
            'attendance_dates' => $attendances->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'date' => $a->attendance_date->format('Y-m-d'),
                'trimester' => $this->getTrimester($a->attendance_date),
                'duration_hours' => $a->duration_hours,
            ]),
            'students' => $studentsData,
        ];
    }

    private function buildRecordsByStudent(Collection $attendances): array
    {
        $map = [];
        foreach ($attendances as $attendance) {
            foreach ($attendance->records as $record) {
                $sid = $record->student_id;
                if (!isset($map[$sid])) {
                    $map[$sid] = collect();
                }
                $map[$sid]->put($attendance->id, $record);
            }
        }
        return $map;
    }

    private function getTrimester(\Carbon\Carbon $date): int
    {
        $month = $date->month;
        if ($month <= 3) return 1;
        if ($month <= 6) return 2;
        if ($month <= 9) return 3;
        return 4;
    }

    private function buildAttendanceMatrix(Collection $attendances, Collection $recordsByAttendance): array
    {
        return $attendances->map(function ($attendance) use ($recordsByAttendance) {
            $record = $recordsByAttendance->get($attendance->id);
            return [
                'attendance_id' => $attendance->id,
                'date' => $attendance->attendance_date->format('Y-m-d'),
                'trimester' => $this->getTrimester($attendance->attendance_date),
                'present' => $record !== null,
                'grade' => $record?->grade ? (float) $record->grade : null,
            ];
        })->values()->all();
    }

    private function buildTrimesterData(Collection $attendances, Collection $recordsByAttendance): array
    {
        $trimesters = [];

        foreach ($attendances->groupBy(fn ($a) => $this->getTrimester($a->attendance_date)) as $trimester => $atts) {
            $present = 0;
            foreach ($atts as $attendance) {
                if ($recordsByAttendance->has($attendance->id)) {
                    $present++;
                }
            }
            $total = $atts->count();
            $trimesters[] = [
                'trimester' => $trimester,
                'present_count' => $present,
                'absent_count' => $total - $present,
                'total' => $total,
                'frequency_pct' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ];
        }

        return $trimesters;
    }

    private function computeFrequency(Collection $attendances, Collection $recordsByAttendance): array
    {
        $present = 0;
        $total = $attendances->count();

        foreach ($attendances as $attendance) {
            if ($recordsByAttendance->has($attendance->id)) {
                $present++;
            }
        }

        return [
            'present_count' => $present,
            'total' => $total,
            'absent_count' => $total - $present,
            'frequency_pct' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];
    }

    private function buildTrimesterGradeBlocks(Collection $trimesterGrades): array
    {
        $blocks = [];
        for ($t = 1; $t <= 4; $t++) {
            $tg = $trimesterGrades->get($t);
            $blocks[] = [
                'trimester' => $t,
                'activity_grade_1' => $tg ? (float) $tg->activity_grade_1 : null,
                'activity_grade_2' => $tg ? (float) $tg->activity_grade_2 : null,
                'activity_grade_3' => $tg ? (float) $tg->activity_grade_3 : null,
                'activities_average' => $tg ? (float) $tg->activities_average : null,
                'au_grade_1' => $tg ? (float) $tg->au_grade_1 : null,
                'au_grade_2' => $tg ? (float) $tg->au_grade_2 : null,
                'au_grade_3' => $tg ? (float) $tg->au_grade_3 : null,
                'au_average' => $tg ? (float) $tg->au_average : null,
                'final_grade' => $tg ? (float) $tg->final_grade : null,
            ];
        }
        return $blocks;
    }
}
