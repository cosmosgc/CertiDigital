<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstructorController extends Controller
{
    public function index()
    {
        $instructors = Instructor::with('certificates')->paginate(15);

        return response()->json($instructors, Response::HTTP_OK);
    }

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

    public function show(Instructor $instructor)
    {
        $instructor->load('certificates');

        return response()->json($instructor, Response::HTTP_OK);
    }

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

    public function destroy(Instructor $instructor)
    {
        $instructor->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
