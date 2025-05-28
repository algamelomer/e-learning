<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuestionOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = QuestionOption::with('question');
        if ($request->has('question_id') && $request->question_id != '') {
            $query->where('question_id', $request->question_id);
        }
        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => ['required', 'uuid', Rule::exists('questions', 'question_id')],
            'option_text' => 'required|string|max:200',
            'is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ensure not too many correct options for single_choice or true_false questions
        // This logic might be better placed in a service or model event listener for more complex scenarios
        $question = \App\Models\Question::find($request->question_id);
        if ($question && ($question->question_type === 'single_choice' || $question->question_type === 'true_false')) {
            if ($request->is_correct) {
                // Set other options for this question to not correct
                QuestionOption::where('question_id', $request->question_id)->update(['is_correct' => false]);
            }
        }

        $questionOption = QuestionOption::create($request->all());
        $questionOption->load('question');
        return response()->json($questionOption, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(QuestionOption $questionOption)
    {
        $questionOption->load('question');
        return response()->json($questionOption);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuestionOption $questionOption)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => ['sometimes', 'required', 'uuid', Rule::exists('questions', 'question_id')],
            'option_text' => 'sometimes|required|string|max:200',
            'is_correct' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $question = \App\Models\Question::find($request->question_id ?? $questionOption->question_id);
        if ($question && ($question->question_type === 'single_choice' || $question->question_type === 'true_false')) {
            if ($request->boolean('is_correct')) {
                QuestionOption::where('question_id', $question->question_id)
                                ->where('option_id', '!=', $questionOption->option_id)
                                ->update(['is_correct' => false]);
            }
        }

        $questionOption->update($request->all());
        $questionOption->load('question');
        return response()->json($questionOption);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuestionOption $questionOption)
    {
        $questionOption->delete();
        return response()->json(null, 204);
    }
}
