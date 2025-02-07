<?php

namespace App\Http\Controllers\Api;

use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\QuestionResource;
use App\Http\Resources\Api\QuizResource;
use App\Models\StudentQuizHistory;

class QuizController extends Controller
{
    public function index()
    {
        $quiz = Quiz::get();
        return QuizResource::collection($quiz);
    }

    public function getQuestion(Request $request)
    {
        $studentId = $request->header('Device-Id');
        $quiz = $request->quiz;
        $limit = $request->limit ?? 10;

        if ($quiz == 'all') {
            $quiz = Quiz::with('questions')->get();

            return QuestionResource::collection($quiz);
        } elseif ($quiz !== null) {
            // Fetch the IDs of questions the student has already taken
            $takenQuestionIds = StudentQuizHistory::
                where('student_id', $studentId)
                ->where('quiz_id', $quiz)
                ->pluck('question_id')
                ->toArray();

            $quiz = Quiz::with([
                'questions' => function ($q) use ($limit, $takenQuestionIds) {
                    $q->whereNotIn('id', $takenQuestionIds) // Exclude questions already taken
                        ->inRandomOrder() // Fetch random questions
                        ->limit($limit);
                }
            ])
                ->findOrFail($quiz);

            return new QuestionResource($quiz);
        } else {
            return response()->json(['error' => 'Invalid request'], 400);
        }

    }
}
