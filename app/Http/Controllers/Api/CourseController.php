<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['students', 'certificates'])->paginate(15);

        return response()->json($courses, Response::HTTP_OK);
    }

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

    public function show(Course $course)
    {
        $course->load(['students', 'certificates']);

        return response()->json($course, Response::HTTP_OK);
    }

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

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
