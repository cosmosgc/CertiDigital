<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::with(['student', 'course', 'instructor'])->paginate(20);

        return response()->json($certificates, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'certificate_code' => 'required|string|unique:certificates,certificate_code',
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'issue_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'verification_url' => 'nullable|url',
            'qr_code_path' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $certificate = Certificate::create($data);

        return response()->json($certificate->load(['student', 'course', 'instructor']), Response::HTTP_CREATED);
    }

    public function show(Certificate $certificate)
    {
        return response()->json($certificate->load(['student', 'course', 'instructor', 'verificationLogs']), Response::HTTP_OK);
    }

    public function update(Request $request, Certificate $certificate)
    {
        $data = $request->validate([
            'student_id' => 'sometimes|required|exists:students,id',
            'course_id' => 'sometimes|required|exists:courses,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'issue_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'verification_url' => 'nullable|url',
            'qr_code_path' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $certificate->update($data);

        return response()->json($certificate->load(['student', 'course', 'instructor']), Response::HTTP_OK);
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    // Verify certificate by its code and record a verification log
    public function verify(Request $request, $code)
    {
        $certificate = Certificate::where('certificate_code', $code)->first();

        if (! $certificate) {
            return response()->json(['message' => 'Certificate not found'], Response::HTTP_NOT_FOUND);
        }

        VerificationLog::create([
            'certificate_id' => $certificate->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'checked_at' => Carbon::now(),
        ]);

        return response()->json([
            'verified' => true,
            'certificate' => $certificate->load(['student', 'course', 'instructor']),
        ], Response::HTTP_OK);
    }

    public function getByCode($code)
    {
        $certificate = Certificate::where('certificate_code', $code)->with(['student', 'course', 'instructor', 'verificationLogs'])->first();

        if (! $certificate) {
            return response()->json(['message' => 'Certificate not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($certificate, Response::HTTP_OK);
    }
}
