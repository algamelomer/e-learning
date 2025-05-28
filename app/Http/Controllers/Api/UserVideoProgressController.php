<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserVideoProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserVideoProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UserVideoProgress::with(['user', 'video']);

        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('video_id') && $request->video_id != '') {
            $query->where('video_id', $request->video_id);
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
            'video_id' => ['required', 'uuid', Rule::exists('videos', 'video_id')],
            'is_completed' => 'required|boolean',
            'last_watched_time' => 'nullable|date', // Or integer if storing seconds
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Prevent duplicate entries for the same user and video
        $existingProgress = UserVideoProgress::where('user_id', $request->user_id)
                                            ->where('video_id', $request->video_id)
                                            ->first();
        if ($existingProgress) {
            return response()->json(['message' => 'User video progress for this video already exists. Use PUT to update.'], 409);
        }

        $progress = UserVideoProgress::create($request->all());
        $progress->load(['user', 'video']);
        return response()->json($progress, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserVideoProgress $userVideoProgress)
    {
        $userVideoProgress->load(['user', 'video']);
        return response()->json($userVideoProgress);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserVideoProgress $userVideoProgress)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['sometimes', 'required', 'uuid', Rule::exists('users', 'user_id'),
                          Rule::unique('user_video_progress')->ignore($userVideoProgress->progress_id, 'progress_id')->where(function ($query) use ($request, $userVideoProgress) {
                              return $query->where('video_id', $request->video_id ?? $userVideoProgress->video_id);
                          })],
            'video_id' => ['sometimes', 'required', 'uuid', Rule::exists('videos', 'video_id'),
                           Rule::unique('user_video_progress')->ignore($userVideoProgress->progress_id, 'progress_id')->where(function ($query) use ($request, $userVideoProgress) {
                               return $query->where('user_id', $request->user_id ?? $userVideoProgress->user_id);
                           })],
            'is_completed' => 'sometimes|required|boolean',
            'last_watched_time' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userVideoProgress->update($request->all());
        $userVideoProgress->load(['user', 'video']);
        return response()->json($userVideoProgress);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserVideoProgress $userVideoProgress)
    {
        $userVideoProgress->delete();
        return response()->json(null, 204);
    }
}
