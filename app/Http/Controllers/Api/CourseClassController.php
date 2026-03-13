<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CourseClassController extends Controller
{
    public function index()
    {
        $classes = CourseClass::with(['course', 'students'])->paginate(15);

        return response()->json($classes, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('course_classes')->where(
                    fn ($query) => $query->where('course_id', $request->input('course_id'))
                ),
            ],
            'description' => 'nullable|string',
        ]);

        $courseClass = CourseClass::create($data);

        return response()->json(
            $courseClass->load(['course', 'students']),
            Response::HTTP_CREATED
        );
    }

    public function show(CourseClass $courseClass)
    {
        return response()->json(
            $courseClass->load(['course', 'enrollments.student', 'students']),
            Response::HTTP_OK
        );
    }

    public function update(Request $request, CourseClass $courseClass)
    {
        $data = $request->validate([
            'course_id' => 'sometimes|required|exists:courses,id',
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique('course_classes')
                    ->ignore($courseClass->id)
                    ->where(fn ($query) => $query->where(
                        'course_id',
                        $request->input('course_id', $courseClass->course_id)
                    )),
            ],
            'description' => 'nullable|string',
        ]);

        $courseClass->update($data);

        return response()->json(
            $courseClass->load(['course', 'students']),
            Response::HTTP_OK
        );
    }

    public function destroy(CourseClass $courseClass)
    {
        $courseClass->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
