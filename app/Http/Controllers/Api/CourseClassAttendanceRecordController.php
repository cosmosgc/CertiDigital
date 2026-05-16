<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseClassAttendance;
use App\Models\CourseClassAttendanceRecord;
use App\Models\CourseEnrollment;
use App\Services\CourseEnrollmentProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseClassAttendanceRecordController extends Controller
{
    public function __construct(private readonly CourseEnrollmentProgressService $progressService)
    {
    }

    /**
     * Store a newly created course class attendance record in storage.
     */
    public function store(Request $request, CourseClassAttendance $courseClassAttendance)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'grade' => 'nullable|numeric|min:0|max:100',
        ]);

        if (!isset($data['grade'])) {
            $data['grade'] = 6;
        }

        $enrollment = CourseEnrollment::where('course_class_id', $courseClassAttendance->course_class_id)
            ->where('student_id', $data['student_id'])
            ->first();

        if (! $enrollment) {
            return response()->json([
                'message' => 'The selected student is not enrolled in this class.',
                'errors' => [
                    'student_id' => ['The selected student is not enrolled in this class.'],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $record = CourseClassAttendanceRecord::firstOrCreate([
            'course_class_attendance_id' => $courseClassAttendance->id,
            'student_id' => $data['student_id'],
        ]);

        $record->update(['grade' => $data['grade']]);

        $this->progressService->refresh($enrollment);

        return response()->json($record->load('student'), Response::HTTP_CREATED);
    }

    /**
     * Update the specified course class attendance record grade.
     */
    public function update(Request $request, CourseClassAttendanceRecord $courseClassAttendanceRecord)
    {
        $data = $request->validate([
            'grade' => 'nullable|numeric|min:0|max:100',
        ]);

        $grade = $data['grade'] ?? 6;
        $courseClassAttendanceRecord->update(['grade' => $grade]);

        return response()->json($courseClassAttendanceRecord->load('student'), Response::HTTP_OK);
    }

    /**
     * Remove the specified course class attendance record from storage and refresh enrollment progress.
     */
    public function destroy(CourseClassAttendanceRecord $courseClassAttendanceRecord)
    {
        $enrollment = CourseEnrollment::where('course_class_id', $courseClassAttendanceRecord->attendance->course_class_id)
            ->where('student_id', $courseClassAttendanceRecord->student_id)
            ->first();

        $courseClassAttendanceRecord->delete();

        if ($enrollment) {
            $this->progressService->refresh($enrollment);
        }

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
