<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Certificate::with(['user', 'course']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        return response()->json($query->latest('issue_date')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'uuid', Rule::exists('users', 'user_id')],
            'course_id' => ['required', 'uuid', Rule::exists('courses', 'course_id')],
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Prevent duplicate certificates for the same user and course
        $existingCertificate = Certificate::where('user_id', $request->user_id)
                                        ->where('course_id', $request->course_id)
                                        ->first();
        if ($existingCertificate) {
            return response()->json(['message' => 'A certificate for this user and course already exists. Use PUT to update.'], 409);
        }

        $certificate = Certificate::create($request->all());
        $certificate->load(['user', 'course']);
        return response()->json($certificate, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Certificate $certificate)
    {
        $certificate->load(['user', 'course']);
        return response()->json($certificate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Certificate $certificate)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['sometimes', 'required', 'uuid', Rule::exists('users', 'user_id')],
            'course_id' => ['sometimes', 'required', 'uuid', Rule::exists('courses', 'course_id')],
            'issue_date' => 'sometimes|required|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $certificate->update($request->all());
        $certificate->load(['user', 'course']);
        return response()->json($certificate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        $certificate->delete();
        return response()->json(null, 204);
    }
}
