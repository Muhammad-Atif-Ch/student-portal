<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\StudentQuizHistory;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\QuizResource;
use App\Http\Resources\Api\QuestionResource;

class QuizController extends Controller
{
    public function index()
    {
        $quiz = Quiz::get();
        return QuizResource::collection($quiz);
    }

    public function searchQuestion(Request $request)
    {
        $includeAnswer = $request->include_answer;

        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();

        // Fetch the IDs of questions the student has already taken
        $query = Question::query();
        $query->with('translations');
        $query->where('type', $user->app_type);
        $query->where('question', 'like', "%{$request->question}%");

        if ($includeAnswer) {
            $query->orWhere(function ($subQ) use ($request) {
                $subQ->where('a', 'like', "%{$request->question}%")
                    ->orWhere('b', 'like', "%{$request->question}%")
                    ->orWhere('c', 'like', "%{$request->question}%")
                    ->orWhere('d', 'like', "%{$request->question}%");
            });
        }

        $questions = $query->get();

        return QuestionResource::collection($questions);
    }

    public function getReadQuestion(Request $request)
    {
        $quiz = $request->quiz;
        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;
        // Step 2: Determine allowed question types
        $allowedTypes = match ($user->app_type) {
            'car' => ['car', 'both'],
            'bike' => ['bike', 'both'],
            'both' => ['car', 'bike', 'both'],
            default => ['both'], // fallback if app_type is missing
        };

        if ($quiz == 'all') {
            $quiz = Quiz::with([
                'questions' => function ($query) use ($allowedTypes) {
                    $query->whereIn('type', $allowedTypes)->with('translations');
                }
            ])->get();

            return QuestionResource::collection($quiz);
        } elseif ($quiz !== null) {
            // Fetch the IDs of questions the student has already taken
            $quiz = Quiz::with([
                'questions' => function ($query) use ($allowedTypes) {
                    $query->whereIn('type', $allowedTypes)->with('translations');
                }
            ])->where('id', $quiz)->get();

            return QuestionResource::collection($quiz);
        } else {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    }

    public function previousIncorrect()
    {
        $deviceId = request()->header('Device-Id');
        $user = User::where('device_id', $deviceId)->firstOrFail();

        // Load all quizzes attempted by the user
        $question = Quiz::with([
            'questions' => function ($q) use ($user) {
                $q->whereHas('previousTestQuestion', function ($pq) use ($user) {
                    $pq->where('correct', 0);
                    $pq->whereHas('previousTest', function ($pq) use ($user) {
                        $pq->where('user_id', $user->id);
                    });
                })
                    ->with([
                        'translations',
                        'previousTestQuestion' => function ($q) use ($user) {
                            $q->where('correct', 0)
                                ->whereHas('previousTest', function ($pq) use ($user) {
                                    $pq->where('user_id', $user->id); // only latest incorrect attempt
                                })
                                ->orderBy('created_at', 'desc') // latest attempt
                                ->limit(1);

                        }
                    ]);
            }
        ])->whereHas('questions.previousTestQuestion', function ($pq) use ($user) {
            $pq->where('correct', 0);
            $pq->whereHas('previousTest', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        })->get();

        return QuestionResource::collection($question);
    }

    public function leastSeen()
    {
        $deviceId = request()->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $quizzes = Quiz::with('questions.translations')->get();


        $filteredQuizzes = $quizzes->map(function ($quiz) use ($user) {
            $userId = $user->id;

            // Get IDs of questions already seen by the user for this quiz
            $seenQuestionIds = StudentQuizHistory::where('user_id', $userId)
                ->where('quiz_id', $quiz->id)
                ->pluck('question_id');

            // Filter out already seen questions
            $unseenQuestions = $quiz->questions->whereNotIn('id', $seenQuestionIds)->whereIn('type', [$user->app_type, 'both']);

            // Attach filtered questions back to the quiz
            $quiz->setRelation('questions', $unseenQuestions);

            return $quiz;
        });

        return QuestionResource::collection($filteredQuizzes);
    }
}
