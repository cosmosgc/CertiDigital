<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseEnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = CourseEnrollment::with(['student', 'course'])->paginate(20);

        return response()->json($enrollments, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'progress_percent' => 'nullable|numeric|min:0|max:100',
            'grade' => 'nullable|numeric|min:0|max:100',
            'completed' => 'nullable|boolean',
        ]);

        $enrollment = CourseEnrollment::create($data);

        return response()->json($enrollment->load(['student', 'course']), Response::HTTP_CREATED);
    }

    public function show(CourseEnrollment $courseEnrollment)
    {
        return response()->json($courseEnrollment->load(['student', 'course']), Response::HTTP_OK);
    }

    public function update(Request $request, CourseEnrollment $courseEnrollment)
    {
        $data = $request->validate([
            'progress_percent' => 'nullable|numeric|min:0|max:100',
            'grade' => 'nullable|numeric|min:0|max:100',
            'completed' => 'nullable|boolean',
        ]);

        $courseEnrollment->update($data);

        return response()->json($courseEnrollment->load(['student', 'course']), Response::HTTP_OK);
    }

    public function destroy(CourseEnrollment $courseEnrollment)
    {
        $courseEnrollment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
