<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstructorController extends Controller
{
    /**
     * Display a listing of instructors with related certificates.
     */
    public function index()
    {
        $instructors = Instructor::with('certificates')->paginate(15);

        return response()->json($instructors, Response::HTTP_OK);
    }

    /**
     * Store a newly created instructor in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:instructors,email',
            'cpf_cnpj' => 'nullable|string|max:50',
            'signature_image' => 'nullable|string',
        ]);

        $instructor = Instructor::create($data);

        return response()->json($instructor, Response::HTTP_CREATED);
    }

    /**
     * Display the specified instructor details with related certificates.
     */
    public function show(Instructor $instructor)
    {
        $instructor->load('certificates');

        return response()->json($instructor, Response::HTTP_OK);
    }

    /**
     * Update the specified instructor in storage.
     */
    public function update(Request $request, Instructor $instructor)
    {
        $data = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:instructors,email,' . $instructor->id,
            'cpf_cnpj' => 'nullable|string|max:50',
            'signature_image' => 'nullable|string',
        ]);

        $instructor->update($data);

        return response()->json($instructor, Response::HTTP_OK);
    }

    /**
     * Remove the specified instructor from storage.
     */
    public function destroy(Instructor $instructor)
    {
        $instructor->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
