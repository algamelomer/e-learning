<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Question::with('video');
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
            'video_id' => ['required', 'uuid', Rule::exists('videos', 'video_id')],
            'question_text' => 'required|string',
            'question_type' => ['required', 'string', Rule::in(['multiple_choice', 'single_choice', 'true_false'])],
            'points' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $question = Question::create($request->all());
        $question->load('video');
        return response()->json($question, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        // Eager load video and also options for this question
        $question->load(['video', 'questionOptions']);
        return response()->json($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => ['sometimes', 'required', 'uuid', Rule::exists('videos', 'video_id')],
            'question_text' => 'sometimes|required|string',
            'question_type' => ['sometimes', 'required', 'string', Rule::in(['multiple_choice', 'single_choice', 'true_false'])],
            'points' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $question->update($request->all());
        $question->load('video');
        return response()->json($question);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        // Consider related question_options - they will cascade delete due to DB constraint
        $question->delete();
        return response()->json(null, 204);
    }
}
