<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseClassAttendance;
use App\Models\CourseClassAttendanceRecord;
use App\Services\CourseEnrollmentProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CourseClassAttendanceController extends Controller
{
    public function __construct(private readonly CourseEnrollmentProgressService $progressService)
    {
    }

    /**
     * Store a newly created course class attendance in storage.
     */
    public function store(Request $request, CourseClass $courseClass)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:150',
            'attendance_date' => 'required|date',
            'duration_hours' => 'nullable|numeric|min:0.25',
        ]);

        $attendance = DB::transaction(function () use ($courseClass, $data) {
            $attendance = CourseClassAttendance::create([
                'course_class_id' => $courseClass->id,
                'name' => $data['name'] ?? null,
                'attendance_date' => $data['attendance_date'],
                'duration_hours' => $data['duration_hours'] ?? 1,
            ]);

            if (blank($attendance->name)) {
                $attendance->update(['name' => 'Attendance #' . $attendance->id]);
            }

            $recordPayload = $courseClass->enrollments()
                ->pluck('student_id')
                ->map(fn ($studentId) => [
                    'course_class_attendance_id' => $attendance->id,
                    'student_id' => $studentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->all();

            if ($recordPayload) {
                CourseClassAttendanceRecord::insert($recordPayload);
            }

            return $attendance->load('records.student');
        });

        $this->refreshClassEnrollments($courseClass);

        return response()->json($attendance, Response::HTTP_CREATED);
    }

    /**
     * Update the specified course class attendance in storage.
     */
    public function update(Request $request, CourseClassAttendance $courseClassAttendance)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:150',
            'attendance_date' => 'required|date',
            'duration_hours' => 'nullable|numeric|min:0.25',
        ]);

        $courseClassAttendance->update([
            'name' => filled($data['name'] ?? null) ? $data['name'] : 'Attendance #' . $courseClassAttendance->id,
            'attendance_date' => $data['attendance_date'],
            'duration_hours' => $data['duration_hours'] ?? 1,
        ]);

        $this->refreshClassEnrollments($courseClassAttendance->courseClass);

        return response()->json($courseClassAttendance->load('records.student'), Response::HTTP_OK);
    }

    /**
     * Remove the specified course class attendance from storage and refresh enrollments.
     */
    public function destroy(CourseClassAttendance $courseClassAttendance)
    {
        $courseClass = $courseClassAttendance->courseClass;
        $courseClassAttendance->delete();

        $this->refreshClassEnrollments($courseClass);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove multiple attendances at once, either by explicit ids or by date.
     * Used by the schedule day-manager to clear holidays quickly.
     */
    public function destroyMany(Request $request)
    {
        $data = $request->validate([
            'ids' => 'nullable|array|min:1|max:500',
            'ids.*' => 'integer|exists:course_class_attendances,id',
            'date' => 'nullable|date',
        ]);

        if (empty($data['ids']) && empty($data['date'])) {
            return response()->json(
                ['message' => 'Informe ids ou date para exclusão em massa.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $query = CourseClassAttendance::query();

        if (! empty($data['ids'])) {
            $query->whereIn('id', $data['ids']);
        } else {
            $query->whereDate('attendance_date', $data['date']);
        }

        $affectedClassIds = (clone $query)->pluck('course_class_id')->unique()->values();

        $deleted = $query->delete();

        foreach (CourseClass::whereIn('id', $affectedClassIds)->get() as $courseClass) {
            $this->refreshClassEnrollments($courseClass);
        }

        return response()->json(['deleted' => $deleted], Response::HTTP_OK);
    }

    /**
     * Refresh enrollment progress for all enrollments in the course class.
     */
    private function refreshClassEnrollments(CourseClass $courseClass): void
    {
        $courseClass->loadMissing('enrollments.course');

        foreach ($courseClass->enrollments as $enrollment) {
            $this->progressService->refresh($enrollment);
        }
    }
}
