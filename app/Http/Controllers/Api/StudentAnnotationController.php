<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseClassAttendanceRecord;
use App\Models\StudentAnnotation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

class StudentAnnotationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
        ]);

        $annotations = StudentAnnotation::with([
            'student',
            'courseClass.course',
            'attendanceRecord.attendance',
        ])
            ->when($request->filled('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->integer('student_id'));
            })
            ->orderByDesc('annotation_date')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($annotations, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $annotation = StudentAnnotation::create($data);

        return response()->json($this->loadRelations($annotation), Response::HTTP_CREATED);
    }

    public function show(StudentAnnotation $studentAnnotation)
    {
        return response()->json($this->loadRelations($studentAnnotation), Response::HTTP_OK);
    }

    public function update(Request $request, StudentAnnotation $studentAnnotation)
    {
        $data = $this->validateData($request, true, $studentAnnotation);

        $studentAnnotation->update($data);

        return response()->json($this->loadRelations($studentAnnotation), Response::HTTP_OK);
    }

    public function destroy(StudentAnnotation $studentAnnotation)
    {
        $studentAnnotation->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function validateData(Request $request, bool $partial = false, ?StudentAnnotation $studentAnnotation = null): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        $data = $request->validate([
            'student_id' => [...$required, 'exists:students,id'],
            'course_class_id' => 'nullable|exists:course_classes,id',
            'course_class_attendance_record_id' => 'nullable|exists:course_class_attendance_records,id',
            'annotation_date' => [...$required, 'date'],
            'warning_level' => [...$required, 'integer', 'min:0', 'max:4'],
            'notes' => [...$required, 'string'],
        ]);

        $studentId = $data['student_id'] ?? $studentAnnotation?->student_id;
        $courseClassId = array_key_exists('course_class_id', $data)
            ? $data['course_class_id']
            : $studentAnnotation?->course_class_id;
        $attendanceRecordId = array_key_exists('course_class_attendance_record_id', $data)
            ? $data['course_class_attendance_record_id']
            : $studentAnnotation?->course_class_attendance_record_id;

        if ($attendanceRecordId) {
            $attendanceRecord = CourseClassAttendanceRecord::with('attendance')->findOrFail($attendanceRecordId);

            if ((int) $attendanceRecord->student_id !== (int) $studentId) {
                throw new HttpResponseException(response()->json([
                    'message' => 'The selected attendance record does not belong to the selected student.',
                    'errors' => [
                        'course_class_attendance_record_id' => ['The selected attendance record does not belong to the selected student.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY));
            }

            $attendanceCourseClassId = $attendanceRecord->attendance?->course_class_id;

            if ($courseClassId !== null && (int) $courseClassId !== (int) $attendanceCourseClassId) {
                throw new HttpResponseException(response()->json([
                    'message' => 'The selected attendance record does not belong to the selected class.',
                    'errors' => [
                        'course_class_attendance_record_id' => ['The selected attendance record does not belong to the selected class.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY));
            }

            $data['course_class_id'] = $attendanceCourseClassId;
        }

        return $data;
    }

    private function loadRelations(StudentAnnotation $studentAnnotation): StudentAnnotation
    {
        return $studentAnnotation->load([
            'student',
            'courseClass.course',
            'attendanceRecord.attendance',
        ]);
    }
}
