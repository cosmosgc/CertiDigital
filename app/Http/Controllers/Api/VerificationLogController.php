<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerificationLogController extends Controller
{
    public function index()
    {
        $logs = VerificationLog::with('certificate')->orderBy('checked_at', 'desc')->paginate(50);

        return response()->json($logs, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'certificate_id' => 'required|exists:certificates,id',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
            'checked_at' => 'nullable|date',
        ]);

        $log = VerificationLog::create($data + ['checked_at' => $data['checked_at'] ?? now()]);

        return response()->json($log->load('certificate'), Response::HTTP_CREATED);
    }

    public function show(VerificationLog $verificationLog)
    {
        return response()->json($verificationLog->load('certificate'), Response::HTTP_OK);
    }

    public function destroy(VerificationLog $verificationLog)
    {
        $verificationLog->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
