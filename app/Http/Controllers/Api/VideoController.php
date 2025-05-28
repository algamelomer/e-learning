<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Allow filtering by course_id if provided in query params
        $query = Video::with('course');
        if ($request->has('course_id') && $request->course_id != '') {
            $query->where('course_id', $request->course_id);
        }
        return response()->json($query->orderBy('order_in_course')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => ['required', 'uuid', Rule::exists('courses', 'course_id')],
            'title' => 'required|string|max:200',
            'duration' => 'nullable|integer|min:0',
            's3_url' => 'nullable|string|max:500', // Consider 'url' rule if format is strict
            'order_in_course' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $video = Video::create($request->all());
        $video->load('course');
        return response()->json($video, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video)
    {
        $video->load('course'); // Eager load course relationship
        return response()->json($video);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Video $video)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => ['sometimes', 'required', 'uuid', Rule::exists('courses', 'course_id')],
            'title' => 'sometimes|required|string|max:200',
            'duration' => 'nullable|integer|min:0',
            's3_url' => 'nullable|string|max:500',
            'order_in_course' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $video->update($request->all());
        $video->load('course');
        return response()->json($video);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video)
    {
        $video->delete();
        return response()->json(null, 204);
    }
}
