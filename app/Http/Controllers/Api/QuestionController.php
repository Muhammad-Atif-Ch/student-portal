<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Models\StudentQuizHistory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateFlagRequest;
use App\Http\Resources\Api\QuestionResource;
use App\Models\Question;

class QuestionController extends Controller
{
    public function getFlag()
    {
        $flagIds = StudentQuizHistory::where('flag', 1)->pluck('question_id')->toArray();
        $questions = Question::whereIn('id', $flagIds)->get();
        return QuestionResource::collection($questions);
    }

    public function storeFlag(CreateFlagRequest $request)
    {
        try {
            $question = StudentQuizHistory::where('question_id', $request->question_id)->firstOrFail();
            $question->flag = $request->flag;
            $question->save();

            return (new QuestionResource($question))->additional(['success' => 'Question flaged successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
