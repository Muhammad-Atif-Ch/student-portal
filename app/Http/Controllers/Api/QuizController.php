<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Setting;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\QuestionHistory;
use App\Models\StudentQuizHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\QuizResource;
use App\Http\Resources\Api\QuestionResource;
use App\Http\Requests\Api\CreateQuestionRequest;

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

        // Fetch the IDs of questions the student has already taken
        $query = Question::query();

        $query->where('question', 'like', "%{$request->question}%");

        if ($includeAnswer) {
            $query->orwhere('a', 'like', "%{$request->question}%")
                ->orWhere('b', 'like', "%{$request->question}%")
                ->orWhere('c', 'like', "%{$request->question}%")
                ->orWhere('d', 'like', "%{$request->question}%");
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
                    $query->whereIn('type', $allowedTypes);
                }
            ])->get();

            return QuestionResource::collection($quiz);
        } elseif ($quiz !== null) {
            // Fetch the IDs of questions the student has already taken
            $quiz = Quiz::with([
                'questions' => function ($query) use ($allowedTypes) {
                    $query->whereIn('type', $allowedTypes);
                }
            ])->where('id', $quiz)->first();

            return new QuestionResource($quiz);
        } else {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    }

    public function getPracticeQuestion(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $quiz = $request->quiz;
        $limit = 40;

        $user = User::where('device_id', $deviceId)->first();
        $userId = $user->id;
        // Step 2: Determine allowed question types
        $allowedTypes = match ($user->app_type) {
            'car' => ['car', 'both'],
            'bike' => ['bike', 'both'],
            'both' => ['car', 'bike', 'both'],
            default => ['both'], // fallback if app_type is missing
        };

        // Determine quiz IDs
        if ($quiz === 'all') {
            $quizIds = Quiz::pluck('id')->toArray();
            $useOfficialLimit = true;
        } elseif ($quiz !== null) {
            $quizIds = is_array($quiz) ? $quiz : explode(',', $quiz);
            $useOfficialLimit = false;
        } else {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $quizzes = Quiz::select('id', 'title', 'official_test_question')
            ->where('id', $quizIds)
            ->get()
            ->map(function ($quiz) use ($userId, $allowedTypes, $useOfficialLimit) {
                $quizId = $quiz->id;
                // $quizLimit = $quiz->official_test_question;
                $quizLimit = $useOfficialLimit ? $quiz->official_test_question : 40;

                // Get previously wrong attempted questions first
                $wrongAttemptedQuestionIds = StudentQuizHistory::where([
                    'user_id' => $userId,
                    'quiz_id' => $quizId,
                    'type' => 'practice',
                    'correct' => 0
                ])->pluck('question_id');

                // Fetch the wrong questions first (limit to quizLimit)
                $wrongQuestions = Question::whereIn('id', $wrongAttemptedQuestionIds)
                    ->where('quiz_id', $quizId)
                    ->whereIn('type', $allowedTypes)
                    ->inRandomOrder()
                    ->limit($quizLimit)
                    ->get();

                $wrongCount = $wrongQuestions->count();
                $remainingLimit = $quizLimit - $wrongCount;

                // STEP 3: Get previously attempted question IDs to avoid
                $alreadyAttemptedIds = QuestionHistory::where([
                    'user_id' => $userId,
                    'quiz_id' => $quizId,
                    'type' => 'practice'
                ])
                    ->pluck('question_id');

                // STEP 4: Fetch remaining random new questions (excluding previously used and wrong ones)
                $remainingQuestions = collect();
                if ($remainingLimit > 0) {
                    $remainingQuestions = Question::whereNotIn('id', $alreadyAttemptedIds)
                        ->whereNotIn('id', $wrongQuestions->pluck('id'))
                        ->where('quiz_id', $quizId)
                        ->whereIn('type', $allowedTypes)
                        ->inRandomOrder()
                        ->limit($remainingLimit)
                        ->get();

                    $actualRemainingCount = $remainingQuestions->count();

                    // STEP 5: If still not enough, reset history and fetch again
                    if ($actualRemainingCount < $remainingLimit) {
                        QuestionHistory::where([
                            'user_id' => $userId,
                            'quiz_id' => $quizId,
                            'type' => 'practice'
                        ])->delete();

                        $needed = $remainingLimit - $actualRemainingCount;

                        $extraQuestions = Question::whereNotIn('id', $wrongQuestions->pluck('id'))
                            ->where('quiz_id', $quizId)
                            ->whereIn('type', $allowedTypes)
                            ->inRandomOrder()
                            ->limit($needed)
                            ->get();

                        $remainingQuestions = $remainingQuestions->merge($extraQuestions);
                    }
                }

                // STEP 6: Combine wrong + new questions
                $finalQuestions = $wrongQuestions->merge($remainingQuestions)->take($quizLimit);

                // STEP 7: Insert into question history
                $historyData = $finalQuestions->map(function ($question) use ($userId, $quizId) {
                    return [
                        'user_id' => $userId,
                        'quiz_id' => $quizId,
                        'question_id' => $question->id,
                        'type' => 'practice',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();

                QuestionHistory::insert($historyData);

                $quiz->setRelation('questions', $finalQuestions);

                return $quiz;
            });

        return new QuestionResource($quizzes);
    }

    public function getOfficialQuestion(Request $request)
    {
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

        $quizzes = Quiz::select('id', 'title', 'official_test_question')
            ->get()
            ->map(function ($quiz) use ($userId, $allowedTypes) {
                $quizId = $quiz->id;
                $quizLimit = $quiz->official_test_question;

                // Get previously wrong attempted questions first
                $wrongAttemptedQuestionIds = StudentQuizHistory::where([
                    'user_id' => $userId,
                    'quiz_id' => $quizId,
                    'type' => 'official',
                    'correct' => 0
                ])->pluck('question_id');

                // Fetch the wrong questions first (limit to quizLimit)
                $wrongQuestions = Question::whereIn('id', $wrongAttemptedQuestionIds)
                    ->where('quiz_id', $quizId)
                    ->whereIn('type', $allowedTypes)
                    ->inRandomOrder()
                    ->limit($quizLimit)
                    ->get();

                $wrongCount = $wrongQuestions->count();
                $remainingLimit = $quizLimit - $wrongCount;

                // STEP 3: Get previously attempted question IDs to avoid
                $alreadyAttemptedIds = QuestionHistory::where([
                    'user_id' => $userId,
                    'quiz_id' => $quizId,
                    'type' => 'official'
                ])
                    ->pluck('question_id');

                // STEP 4: Fetch remaining random new questions (excluding previously used and wrong ones)
                $remainingQuestions = collect();
                if ($remainingLimit > 0) {
                    $remainingQuestions = Question::whereNotIn('id', $alreadyAttemptedIds)
                        ->whereNotIn('id', $wrongQuestions->pluck('id'))
                        ->where('quiz_id', $quizId)
                        ->whereIn('type', $allowedTypes)
                        ->inRandomOrder()
                        ->limit($remainingLimit)
                        ->get();

                    $actualRemainingCount = $remainingQuestions->count();

                    // STEP 5: If still not enough, reset history and fetch again
                    if ($actualRemainingCount < $remainingLimit) {
                        QuestionHistory::where([
                            'user_id' => $userId,
                            'quiz_id' => $quizId,
                            'type' => 'official'
                        ])->delete();

                        $needed = $remainingLimit - $actualRemainingCount;

                        $extraQuestions = Question::whereNotIn('id', $wrongQuestions->pluck('id'))
                            ->where('quiz_id', $quizId)
                            ->whereIn('type', $allowedTypes)
                            ->inRandomOrder()
                            ->limit($needed)
                            ->get();

                        $remainingQuestions = $remainingQuestions->merge($extraQuestions);
                    }
                }

                // STEP 6: Combine wrong + new questions
                $finalQuestions = $wrongQuestions->merge($remainingQuestions)->take($quizLimit);

                // STEP 7: Insert into question history
                $historyData = $finalQuestions->map(function ($question) use ($userId, $quizId) {
                    return [
                        'user_id' => $userId,
                        'quiz_id' => $quizId,
                        'question_id' => $question->id,
                        'type' => 'official',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();

                QuestionHistory::insert($historyData);

                $quiz->setRelation('questions', $finalQuestions);

                return $quiz;
            });

        return new QuestionResource($quizzes);
    }

    public function store(CreateQuestionRequest $request)
    {
        try {
            $deviceId = $request->header('Device-Id');
            $userId = User::where('device_id', $deviceId)->first()->id;
            $validatedData = $request->validated();

            // Use database transaction for data integrity
            $result = DB::transaction(function () use ($validatedData, $userId) {
                $bulkData = [];
                $now = Carbon::now();

                foreach ($validatedData['data'] as $item) {
                    $bulkData[] = [
                        'user_id' => $userId,
                        'quiz_id' => $item['quiz_id'],
                        'question_id' => $item['question_id'],
                        'answer' => $item['answer'],
                        'correct' => $item['correct'],
                        'type' => $item['type'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Bulk insert
                StudentQuizHistory::insert($bulkData);

                return [
                    'inserted_count' => count($bulkData),
                    'user_id' => $userId
                ];
            });

            $now = Carbon::now();
            $result = StudentQuizHistory::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as test_datetime, 
                    type,
                    SUM(CASE WHEN correct = 1 THEN 1 ELSE 0 END) as correct_answers,
                    SUM(CASE WHEN correct = 0 THEN 1 ELSE 0 END) as incorrect_answers,
                    COUNT(*) as total_attempts")
                ->where('user_id', $userId)
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [
                    date('Y-m-d H:i', strtotime($now->format('Y-m-d H:i')))
                ])
                ->groupBy('test_datetime', 'type')
                ->orderBy('test_datetime', 'DESC')
                ->get();

            return response()->json(['data' => $result, 'success' => 'Quiz history saved successfully'], 200);
        } catch (Exception $e) {
            Log::error('Insert failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function previousIncorrect()
    {
        $studentQuizHistory = StudentQuizHistory::where('correct', 0)->pluck('question_id')->toArray();

        $question = Question::with('quiz')->whereIn('id', $studentQuizHistory)->get();

        return QuestionResource::collection($question);
    }

    public function leastSeen()
    {
        $deviceId = request()->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $userId = $user->id;

        $quizzes = Quiz::with('questions')->get();

        $filteredQuizzes = $quizzes->map(function ($quiz) use ($userId) {
            // Get IDs of questions already seen by the user for this quiz
            $seenQuestionIds = QuestionHistory::where('user_id', $userId)
                ->where('quiz_id', $quiz->id)
                ->pluck('question_id');

            // Filter out already seen questions
            $unseenQuestions = $quiz->questions->whereNotIn('id', $seenQuestionIds);

            // Attach filtered questions back to the quiz
            $quiz->setRelation('questions', $unseenQuestions);

            return $quiz;
        });

        return QuestionResource::collection($filteredQuizzes);
    }
}
