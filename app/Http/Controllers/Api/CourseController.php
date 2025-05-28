<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Import Rule for exists validation

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load relationships for better performance if needed frequently
        return response()->json(Course::with(['category', 'instructor'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200|unique:courses,title',
            'description' => 'nullable|string',
            'instructor_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'user_id'), // Ensure instructor_id exists in users table user_id column
            ],
            'category_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'category_id'), // Ensure category_id exists in categories table category_id column
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $course = Course::create($request->all());
        $course->load(['category', 'instructor']); // Load relationships for the created resource
        return response()->json($course, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $course->load(['category', 'instructor', 'videos']); // Eager load relationships
        return response()->json($course);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:200|unique:courses,title,' . $course->course_id . ',course_id',
            'description' => 'nullable|string',
            'instructor_id' => [
                'sometimes',
                'required',
                'uuid',
                Rule::exists('users', 'user_id'),
            ],
            'category_id' => [
                'sometimes',
                'required',
                'uuid',
                Rule::exists('categories', 'category_id'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $course->update($request->all());
        $course->load(['category', 'instructor']); // Load relationships for the updated resource
        return response()->json($course);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json(null, 204);
    }
}
