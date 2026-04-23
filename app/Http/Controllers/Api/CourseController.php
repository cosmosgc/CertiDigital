<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    /**
     * Display a listing of courses with related data (classes, students, certificates).
     */
    public function index()
    {
        $courses = Course::with(['classes.students', 'students', 'certificates'])->paginate(15);

        return response()->json($courses, Response::HTTP_OK);
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workload_hours' => 'nullable|integer|min:0',
            'modality' => 'nullable|string|max:100',
        ]);

        $course = Course::create($data);

        return response()->json($course, Response::HTTP_CREATED);
    }

    /**
     * Display the specified course details with related data.
     */
    public function show(Course $course)
    {
        $course->load(['classes.students', 'students', 'certificates']);

        return response()->json($course, Response::HTTP_OK);
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'workload_hours' => 'nullable|integer|min:0',
            'modality' => 'nullable|string|max:100',
        ]);

        $course->update($data);

        return response()->json($course, Response::HTTP_OK);
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
