<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserCourseProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UserCourseProgress::with(['user', 'course']);

        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('course_id') && $request->course_id != '') {
            $query->where('course_id', $request->course_id);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'uuid', Rule::exists('users', 'user_id')],
            'course_id' => ['required', 'uuid', Rule::exists('courses', 'course_id')],
            'completion_percentage' => 'required|numeric|min:0|max:100',
            'last_accessed' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Prevent duplicate entries for the same user and course
        $existingProgress = UserCourseProgress::where('user_id', $request->user_id)
                                            ->where('course_id', $request->course_id)
                                            ->first();
        if ($existingProgress) {
            return response()->json(['message' => 'User course progress for this course already exists. Use PUT to update.'], 409); // 409 Conflict
        }

        $progress = UserCourseProgress::create($request->all());
        $progress->load(['user', 'course']);
        return response()->json($progress, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserCourseProgress $userCourseProgress)
    {
        $userCourseProgress->load(['user', 'course']);
        return response()->json($userCourseProgress);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserCourseProgress $userCourseProgress)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['sometimes', 'required', 'uuid', Rule::exists('users', 'user_id'),
                          Rule::unique('user_course_progress')->ignore($userCourseProgress->user_course_id, 'user_course_id')->where(function ($query) use ($request, $userCourseProgress) {
                              return $query->where('course_id', $request->course_id ?? $userCourseProgress->course_id);
                          })],
            'course_id' => ['sometimes', 'required', 'uuid', Rule::exists('courses', 'course_id'),
                            Rule::unique('user_course_progress')->ignore($userCourseProgress->user_course_id, 'user_course_id')->where(function ($query) use ($request, $userCourseProgress) {
                                return $query->where('user_id', $request->user_id ?? $userCourseProgress->user_id);
                            })],
            'completion_percentage' => 'sometimes|required|numeric|min:0|max:100',
            'last_accessed' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userCourseProgress->update($request->all());
        $userCourseProgress->load(['user', 'course']);
        return response()->json($userCourseProgress);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserCourseProgress $userCourseProgress)
    {
        $userCourseProgress->delete();
        return response()->json(null, 204);
    }
}
