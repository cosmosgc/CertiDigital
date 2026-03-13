<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseEnrollmentController extends Controller
{
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
            'progress_percent' => 'nullable|numeric|min:0|max:100',
            'grade' => 'nullable|numeric|min:0|max:100',
            'completed' => 'nullable|boolean',
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

        $enrollment = CourseEnrollment::create($data);

        return response()->json($enrollment->load(['student', 'course', 'courseClass']), Response::HTTP_CREATED);
    }

    public function show(CourseEnrollment $courseEnrollment)
    {
        return response()->json($courseEnrollment->load(['student', 'course', 'courseClass']), Response::HTTP_OK);
    }

    public function update(Request $request, CourseEnrollment $courseEnrollment)
    {
        $data = $request->validate([
            'course_class_id' => 'nullable|exists:course_classes,id',
            'progress_percent' => 'nullable|numeric|min:0|max:100',
            'grade' => 'nullable|numeric|min:0|max:100',
            'completed' => 'nullable|boolean',
        ]);

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

        return response()->json($courseEnrollment->load(['student', 'course', 'courseClass']), Response::HTTP_OK);
    }

    public function destroy(CourseEnrollment $courseEnrollment)
    {
        $courseEnrollment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
