<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseClassAttendanceRecord;
use App\Models\CourseEnrollment;
use App\Services\CourseEnrollmentProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseEnrollmentController extends Controller
{
    public function __construct(private readonly CourseEnrollmentProgressService $progressService)
    {
    }

    public function index()
    {
        $enrollments = CourseEnrollment::with(['student', 'course', 'courseClass'])->paginate(20);

        return response()->json($enrollments, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'course_class_id' => 'nullable|exists:course_classes,id',
            'grade' => 'nullable|numeric|min:0|max:100',
        ]);

        if (! empty($data['course_class_id'])) {
            $courseClass = CourseClass::findOrFail($data['course_class_id']);

            if ($courseClass->course_id !== (int) $data['course_id']) {
                return response()->json([
                    'message' => 'The selected class does not belong to the selected course.',
                    'errors' => [
                        'course_class_id' => ['The selected class does not belong to the selected course.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $enrollment = CourseEnrollment::create([
            ...$data,
            'progress_hours' => 0,
            'completed' => false,
        ]);

        if ($enrollment->course_class_id) {
            $enrollment = $this->progressService->refresh($enrollment);
        }

        return response()->json($enrollment->load(['student', 'course', 'courseClass']), Response::HTTP_CREATED);
    }

    public function show(CourseEnrollment $courseEnrollment)
    {
        return response()->json($courseEnrollment->load(['student', 'course', 'courseClass']), Response::HTTP_OK);
    }

    public function update(Request $request, CourseEnrollment $courseEnrollment)
    {
        $originalClassId = $courseEnrollment->course_class_id;

        $data = $request->validate([
            'course_class_id' => 'nullable|exists:course_classes,id',
            'grade' => 'nullable|numeric|min:0|max:100',
            'completed' => 'sometimes|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'frozen' => 'sometimes|boolean',
        ]);

        $manualEnrollmentFields = collect($data)
            ->only(['grade', 'completed', 'start_date', 'end_date', 'frozen'])
            ->all();

        if (! empty($data['course_class_id'])) {
            $courseClass = CourseClass::findOrFail($data['course_class_id']);

            if ($courseClass->course_id !== $courseEnrollment->course_id) {
                return response()->json([
                    'message' => 'The selected class does not belong to the enrollment course.',
                    'errors' => [
                        'course_class_id' => ['The selected class does not belong to the enrollment course.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $courseEnrollment->update($data);
        $courseEnrollment = $courseEnrollment->fresh();

        if ($originalClassId && $originalClassId !== $courseEnrollment->course_class_id) {
            CourseClassAttendanceRecord::where('student_id', $courseEnrollment->student_id)
                ->whereHas('attendance', function ($query) use ($originalClassId) {
                    $query->where('course_class_id', $originalClassId);
                })
                ->delete();
        }

        if ($courseEnrollment->course_class_id) {
            $courseEnrollment = $this->progressService->refresh($courseEnrollment);
        } else {
            $courseEnrollment->update([
                'progress_hours' => 0,
                'completed' => false,
            ]);
        }

        if ($manualEnrollmentFields) {
            $courseEnrollment->update($manualEnrollmentFields);
            $courseEnrollment = $courseEnrollment->fresh();
        }

        return response()->json($courseEnrollment->load(['student', 'course', 'courseClass']), Response::HTTP_OK);
    }

    public function destroy(CourseEnrollment $courseEnrollment)
    {
        if ($courseEnrollment->course_class_id) {
            CourseClassAttendanceRecord::where('student_id', $courseEnrollment->student_id)
                ->whereHas('attendance', function ($query) use ($courseEnrollment) {
                    $query->where('course_class_id', $courseEnrollment->course_class_id);
                })
                ->delete();
        }

        $courseEnrollment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
