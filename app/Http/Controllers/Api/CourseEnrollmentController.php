<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseClassAttendanceRecord;
use App\Models\CourseEnrollment;
use App\Models\CourseEnrollmentTrimesterGrade;
use App\Services\CourseEnrollmentProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseEnrollmentController extends Controller
{
    public function __construct(private readonly CourseEnrollmentProgressService $progressService) {}
    /**
     * Display a listing of course enrollments with related data.
     */
    public function index()
    {
        $enrollments = CourseEnrollment::with(['student', 'course', 'courseClass', 'trimesterGrades'])->paginate(20);

        return response()->json($enrollments, Response::HTTP_OK);
    }

    /**
     * Store a newly created course enrollment in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'course_class_id' => 'nullable|exists:course_classes,id',
            'grade' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'frozen' => 'sometimes|boolean',
        ]);

        if (!isset($data['grade'])) {
            $data['grade'] = 6;
        }

        if (empty($data['start_date'])) {
            $data['start_date'] = now();
        }

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
        ]);

        if ($enrollment->course_class_id) {
            $enrollment = $this->progressService->refresh($enrollment);
        }

        return response()->json($enrollment->load(['student', 'course', 'courseClass', 'trimesterGrades']), Response::HTTP_CREATED);
    }

    /**
     * Display the specified course enrollment details with related data.
     */
    public function show(CourseEnrollment $courseEnrollment)
    {
        return response()->json($courseEnrollment->load(['student', 'course', 'courseClass', 'trimesterGrades']), Response::HTTP_OK);
    }

    /**
     * Update the specified course enrollment in storage.
     */
    public function update(Request $request, CourseEnrollment $courseEnrollment)
    {
        $data = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'course_id' => 'sometimes|exists:courses,id',
            'course_class_id' => 'nullable|exists:course_classes,id',
            'grade' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'frozen' => 'sometimes|boolean',
        ]);

        if (array_key_exists('grade', $data) && $data['grade'] === null) {
            $data['grade'] = 6;
        }

        if (array_key_exists('course_class_id', $data) && !empty($data['course_class_id'])) {
            $courseClass = CourseClass::findOrFail($data['course_class_id']);
            $courseId = $data['course_id'] ?? $courseEnrollment->course_id;
            if ($courseClass->course_id !== (int) $courseId) {
                return response()->json([
                    'message' => 'The selected class does not belong to the selected course.',
                    'errors' => [
                        'course_class_id' => ['The selected class does not belong to the selected course.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $courseEnrollment->update($data);

        if ($courseEnrollment->course_class_id) {
            $courseEnrollment = $this->progressService->refresh($courseEnrollment);
        }

        return response()->json(
            $courseEnrollment->load(['student', 'course', 'courseClass', 'trimesterGrades']),
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified course enrollment from storage.
     */
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
