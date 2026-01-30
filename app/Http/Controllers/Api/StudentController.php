<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['courses', 'certificates'])->paginate(15);

        return response()->json($students, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'document_id' => 'nullable|string|max:255',
        ]);

        $student = Student::create($data);

        return response()->json($student, Response::HTTP_CREATED);
    }

    public function show(Student $student)
    {
        $student->load(['courses', 'certificates']);

        return response()->json($student, Response::HTTP_OK);
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:students,email,' . $student->id,
            'document_id' => 'nullable|string|max:255',
        ]);

        $student->update($data);

        return response()->json($student, Response::HTTP_OK);
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
