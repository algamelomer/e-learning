<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuizAttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = QuizAttempt::with(['user', 'question', 'selectedOption']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('question_id')) {
            $query->where('question_id', $request->question_id);
        }
        // Potentially filter by course or video by joining through question

        return response()->json($query->latest('attempt_time')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'uuid', Rule::exists('users', 'user_id')],
            'question_id' => ['required', 'uuid', Rule::exists('questions', 'question_id')],
            'selected_option_id' => ['required', 'uuid', Rule::exists('question_options', 'option_id')],
            'is_correct' => 'nullable|boolean',
            'attempt_time' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();

        // Auto-set is_correct if not provided
        if (!isset($data['is_correct'])) {
            $selectedOption = QuestionOption::find($data['selected_option_id']);
            if ($selectedOption) {
                $data['is_correct'] = $selectedOption->is_correct;
            }
        }

        // Prevent duplicate attempts for the same user and question if desired (more for actual quiz taking than admin)
        // For admin, allowing multiple entries might be fine, or an update might be preferred.
        // For now, allowing multiple attempts by direct creation via admin.

        $quizAttempt = QuizAttempt::create($data);
        $quizAttempt->load(['user', 'question', 'selectedOption']);
        return response()->json($quizAttempt, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(QuizAttempt $quizAttempt) // quiz_attempt
    {
        $quizAttempt->load(['user', 'question', 'selectedOption']);
        return response()->json($quizAttempt);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuizAttempt $quizAttempt) // quiz_attempt
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['sometimes', 'required', 'uuid', Rule::exists('users', 'user_id')],
            'question_id' => ['sometimes', 'required', 'uuid', Rule::exists('questions', 'question_id')],
            'selected_option_id' => ['sometimes', 'required', 'uuid', Rule::exists('question_options', 'option_id')],
            'is_correct' => 'nullable|boolean',
            'attempt_time' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        // Auto-set is_correct if selected_option_id is changed and is_correct is not explicitly provided
        if ($request->has('selected_option_id') && !isset($data['is_correct'])) {
            $selectedOption = QuestionOption::find($data['selected_option_id']);
             if ($selectedOption) {
                $data['is_correct'] = $selectedOption->is_correct;
            }
        } else if (!isset($data['is_correct'])) {
            // if selected_option_id is not being changed, and is_correct is not sent, keep original
            unset($data['is_correct']);
        }

        $quizAttempt->update($data);
        $quizAttempt->load(['user', 'question', 'selectedOption']);
        return response()->json($quizAttempt);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuizAttempt $quizAttempt) // quiz_attempt
    {
        $quizAttempt->delete();
        return response()->json(null, 204);
    }
}
