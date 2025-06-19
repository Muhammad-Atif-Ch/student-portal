<?php

namespace App\Http\Controllers\Api;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\ResultResource;
use App\Models\Question;
use App\Models\StudentQuizHistory;

class ResultController extends Controller
{
    public function previousTestResult(Request $request)
    {
        $deviceId = $request->header('Device-Id');

        $userId = User::where('device_id', $deviceId)->first()->id;

        $result = StudentQuizHistory::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as test_datetime, 
        type,
        SUM(CASE WHEN correct = 1 THEN 1 ELSE 0 END) as correct_answers,
        SUM(CASE WHEN correct = 0 THEN 1 ELSE 0 END) as incorrect_answers,
        COUNT(*) as total_attempts")
            ->where('user_id', $userId)
            ->groupBy('test_datetime', 'type')
            ->orderBy('test_datetime', 'DESC')
            ->get();
        return ResultResource::collection($result);
    }

    public function previousTestResultReport(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $userId = User::where('device_id', $deviceId)->first()->id;
        // Fetching the test results grouped by date and type   
        $quizzes = Quiz::
            with([
                'questions.studentQuizHistories' => function ($query) use ($userId, $request) {
                    $query->where('user_id', $userId);
                    $query->where('type', $request->type);
                    $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [
                        date('Y-m-d H:i', strtotime($request->date))
                    ]);
                }
            ])
            ->get()
            ->map(function ($quiz) {
                $attempted = 0;
                $correct = 0;
                $incorrect = 0;

                foreach ($quiz->questions as $question) {
                    foreach ($question->studentQuizHistories as $history) {
                        $attempted++;
                        if ($history->correct) {
                            $correct++;
                        } else {
                            $incorrect++;
                        }
                    }
                }

                return [
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'attempted_questions' => $attempted,
                    'correct_answers' => $correct,
                    'incorrect_answers' => $incorrect,
                ];
            });
        return ResultResource::collection($quizzes);
    }

    public function previousTestResultDetails(Request $request)
    {
        $deviceId = $request->header('Device-Id');

        $userId = User::where('device_id', $deviceId)->first()->id;

        $resultIds = StudentQuizHistory::with('question')->where('user_id', $userId)
            ->where('type', $request->type)
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [
                date('Y-m-d H:i', strtotime($request->created_at))
            ])
            ->orderBy('created_at', 'DESC')
            ->get();

        return ResultResource::collection($resultIds);
    }

    public function resultSummary(Request $request)
    {
        $deviceId = $request->header('Device-Id');

        $userId = User::where('device_id', $deviceId)->first()->id;

        $totalQuestions = Question::count();

        $result = StudentQuizHistory::where('user_id', $userId)
            ->where('type', 'official')
            ->selectRaw('
                COUNT(DISTINCT question_id) as total_seen_questions,
                COUNT(*) as total_attempted_questions,
                SUM(CASE WHEN correct = 1 THEN 1 ELSE 0 END) as correct_answers,
                SUM(CASE WHEN correct = 0 THEN 1 ELSE 0 END) as incorrect_answers
            ')
            ->first();

        $response = [
            'total_questions' => $totalQuestions, // Total questions in the database
            'questions_seen' => $result->total_seen_questions,  // Total unique questions seen
            'total_attempted_questions' => $result->total_attempted_questions, // Total attempts
            'correct_ratio' => (int) $result->correct_answers,
            'incorrect_ratio' => (int) $result->incorrect_answers
        ];

        return new ResultResource($response);
    }

    public function resultCategory(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $userId = User::where('device_id', $deviceId)->first()->id;

        $quizzes = Quiz::withCount('questions')
            ->with([
                'questions.studentQuizHistories' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ])
            ->get()
            ->map(function ($quiz) {
                $attempted = 0;
                $correct = 0;
                $incorrect = 0;

                foreach ($quiz->questions as $question) {
                    foreach ($question->studentQuizHistories as $history) {
                        $attempted++;
                        if ($history->correct) {
                            $correct++;
                        } else {
                            $incorrect++;
                        }
                    }
                }

                return [
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'total_questions' => $quiz->questions_count,
                    'attempted_questions' => $attempted,
                    'correct_answers' => $correct,
                    'incorrect_answers' => $incorrect,
                ];
            });

        return ResultResource::collection($quizzes);
    }
}
