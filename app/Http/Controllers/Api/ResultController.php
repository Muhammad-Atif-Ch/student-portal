<?php

namespace App\Http\Controllers\Api;

use App\Models\Quiz;
use App\Models\User;
use App\Models\Question;
use App\Models\PreviousTest;
use Illuminate\Http\Request;
use App\Models\PreviousTestQuiz;
use App\Models\StudentQuizHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ResultResource;
use App\Http\Resources\Api\QuestionResource;

class ResultController extends Controller
{
    public function previousTestResult(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;

        $result = PreviousTest::with('previousTestQuizes.quiz')->where('user_id', $userId)->where('question_type', $user->app_type)->get();

        return ResultResource::collection($result);
    }

    public function previousTestResultReport(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;

        $previousTest = PreviousTest::with('previousTestQuizes')
            ->where('user_id', $userId)
            ->where('question_type', $user->app_type)
            ->findOrFail($request->id);

        $quizStats = PreviousTestQuiz::where('previous_test_id', $previousTest->id)
            ->select(
                'quiz_id',
                DB::raw("SUM(CASE WHEN correct = 1 THEN 1 ELSE 0 END) as correct_answers"),
                DB::raw("SUM(CASE WHEN correct = 0 THEN 1 ELSE 0 END) as incorrect_answers"),
                DB::raw("COUNT(*) as attempted_questions")
            )
            ->groupBy('quiz_id')
            ->get();

        $result = $quizStats->map(function ($stat) {
            $quiz = Quiz::withCount('questions')->find($stat->quiz_id);

            return [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
                'total_questions' => $quiz->questions_count,
                'attempted_questions' => $stat->attempted_questions,
                'correct_answers' => $stat->correct_answers,
                'incorrect_answers' => $stat->incorrect_answers,
            ];
        });

        return new QuestionResource($result);
    }

    public function previousTestResultDetails(Request $request)
    {
        $deviceId = $request->header('Device-Id');

        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;

        $previousTest = PreviousTest::with('previousTestQuizes')
            ->where('user_id', $userId)
            ->findOrFail($request->id);

        // Collect quiz_ids and question_ids
        $quizIds = $previousTest->previousTestQuizes->pluck('quiz_id')->unique();
        $questionIds = $previousTest->previousTestQuizes->pluck('question_id');
        $previousTestId = $previousTest->id;

        // Load quizzes with only the attempted questions
        $results = Quiz::with([
            'questions' => function ($q) use ($questionIds, $previousTestId) {
                $q->whereIn('id', $questionIds)
                    ->with([
                        'translations',
                        'previousTestQuestion' => function ($pq) use ($previousTestId) {
                            $pq->where('previous_test_id', $previousTestId);
                        }
                    ]);
            }
        ])->whereIn('id', $quizIds)->get();

        return QuestionResource::collection($results);
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


        return new QuestionResource($response);
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



        return QuestionResource::collection($quizzes);
    }
}
