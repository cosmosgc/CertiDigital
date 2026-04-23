<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    /**
     * Display a listing of students with related courses and certificates.
     */
    public function index()
    {
        $students = Student::with(['courses', 'certificates'])->paginate(15);

        return response()->json($students, Response::HTTP_OK);
    }

    /**
     * Store a newly created student in storage.
     */
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

    /**
     * Display the specified student details with related courses and certificates.
     */
    public function show(Student $student)
    {
        $student->load(['courses', 'certificates']);

        return response()->json($student, Response::HTTP_OK);
    }

    /**
     * Display detailed information about a student with complete related data (courses, certificates, enrollments, annotations).
     */
    public function detail(Student $student)
    {
        $student->load([
            'courses',
            'certificates',
            'enrollments.course',
            'enrollments.courseClass.instructor',
            'annotations.courseClass.course',
            'annotations.attendanceRecord.attendance',
        ]);

        return response()->json($student, Response::HTTP_OK);
    }

    /**
     * Update the specified student in storage.
     */
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

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
