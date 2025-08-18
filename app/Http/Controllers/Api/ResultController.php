<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Question;
use App\Models\PreviousTest;
use Illuminate\Http\Request;
use App\Models\StudentQuizHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\ResultResource;

class ResultController extends Controller
{
    public function previousTestResult(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;

        $result = PreviousTest::where('user_id', $userId)->where('question_type', $user->app_type)->get();

        return ResultResource::collection($result);
    }

    public function previousTestResultReport(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;
        $date = Carbon::parse($request->date);
        // Fetching the test results grouped by date and type   
        $result = PreviousTest::where('user_id', $userId)->where('question_type', $user->app_type)->where('type', $request->type)->where('test_datetime', $date)->get();

        return ResultResource::collection($result);
    }

    public function previousTestResultDetails(Request $request)
    {
        $deviceId = $request->header('Device-Id');

        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;

        $previousTest = PreviousTest::where('user_id', $userId)->findOrFail($request->id);
        $questionIds = json_decode($previousTest->question_ids, true);

        $results = Quiz::with([
            'questions' => function ($q) use ($questionIds) {
                $q->whereIn('id', $questionIds)
                    ->with('translations'); // nested eager load
            }
        ])->get();

        return ResultResource::collection($results);
    }

    public function resultSummary(Request $request)
    {
        $deviceId = $request->header('Device-Id');

        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;

        $totalQuestions = Question::whereIn('type', [$user->app_type, 'both'])->count();

        $result = StudentQuizHistory::where('user_id', $userId)
            ->where('student_quiz_histories.type', 'official')  // Specify table name for type column
            ->join('questions', 'student_quiz_histories.question_id', '=', 'questions.id')
            ->whereIn('questions.type', [$user->app_type, 'both'])
            ->selectRaw('
                COUNT(DISTINCT questions.id) as questions_seen,
                COUNT(*) as total_attempts,
                SUM(CASE WHEN student_quiz_histories.correct = 1 THEN 1 ELSE 0 END) as correct_count,
                SUM(CASE WHEN student_quiz_histories.correct = 0 THEN 1 ELSE 0 END) as incorrect_count
            ')
            ->first();

        $response = [
            'total_questions' => $totalQuestions, // Total questions in the database
            'questions_seen' => $result->questions_seen,  // Total unique questions seen
            'correct_ratio' => (int) $result->correct_count,
            'incorrect_ratio' => (int) $result->incorrect_count
        ];


        return new ResultResource($response);
    }

    public function resultCategory(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;

        $quizzes = Quiz::withCount([
            // Total questions (only matching type)
            'questions as total_questions' => function ($q) use ($user) {
                $q->whereIn('type', [$user->app_type, 'both']);
            },

            // Attempted questions
            'questions as attempted_questions' => function ($q) use ($userId, $user) {
                $q->whereIn('type', [$user->app_type, 'both'])
                    ->whereHas('studentQuizHistories', function ($sqh) use ($userId) {
                        $sqh->where('user_id', $userId);
                    });
            },

            // Correct answers
            'questions as correct_answers' => function ($q) use ($userId, $user) {
                $q->whereIn('type', [$user->app_type, 'both'])
                    ->whereHas('studentQuizHistories', function ($sqh) use ($userId) {
                        $sqh->where('user_id', $userId)
                            ->where('correct', 1);
                    });
            },

            // Incorrect answers
            'questions as incorrect_answers' => function ($q) use ($userId, $user) {
                $q->whereIn('type', [$user->app_type, 'both'])
                    ->whereHas('studentQuizHistories', function ($sqh) use ($userId) {
                        $sqh->where('user_id', $userId)
                            ->where('correct', 0);
                    });
            },
        ])
            ->get()
            ->map(function ($quiz) {
                return [
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'total_questions' => $quiz->total_questions,
                    'attempted_questions' => $quiz->attempted_questions,
                    'correct_answers' => $quiz->correct_answers,
                    'incorrect_answers' => $quiz->incorrect_answers,
                ];
            });



        return ResultResource::collection($quizzes);
    }
}
