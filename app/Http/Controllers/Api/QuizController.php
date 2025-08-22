<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Setting;
use App\Models\Question;
use App\Models\PreviousTest;
use Illuminate\Http\Request;
use App\Models\QuestionHistory;
use App\Models\PreviousTestQuiz;
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
            ->with('questions')
            ->whereIn('id', $quizIds)
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

                $finalQuestions->load('translations');
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
            ->with('questions')
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
                $wrongQuestions = Question::with('translations')->whereIn('id', $wrongAttemptedQuestionIds)
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
                    $remainingQuestions = Question::with('translations')->whereNotIn('id', $alreadyAttemptedIds)
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

                        $extraQuestions = Question::with('translations')->whereNotIn('id', $wrongQuestions->pluck('id'))
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
                $finalQuestions->load('translations');
                $quiz->setRelation('questions', $finalQuestions);

                return $quiz;
            });

        return new QuestionResource($quizzes);
    }

    public function store(CreateQuestionRequest $request)
    {
        try {
            $deviceId = $request->header('Device-Id');
            $user = User::where('device_id', $deviceId)->first();

            $validatedData = $request->validated();
            $now = Carbon::now();

            // Use database transaction for data integrity
            $result = DB::transaction(function () use ($validatedData, $user, $now) {
                $correct = 0;
                $inCorrect = 0;
                $total = 0;
                $type = "";
                $bulkData = [];
                $quizWiseQuestions = [];
                $userId = $user->id;

                foreach ($validatedData['data'] as $item) {
                    $total++;
                    $type = $item['type'];
                    if ($item['correct'] == 1) {
                        $correct++;
                    } else {
                        $inCorrect++;
                    }
                    $quizWiseQuestions[$item['quiz_id']][] = $item['question_id'];

                    $studentHistory = StudentQuizHistory::where('user_id', $userId)
                        ->where('quiz_id', $item['quiz_id'])
                        ->where('question_id', $item['question_id'])
                        ->first();
                    if ($studentHistory) {
                        $updated = $studentHistory->update([
                            'answer' => $item['answer'],
                            'correct' => $item['correct'],
                            'updated_at' => $now
                        ]);

                        // If old record updated → skip inserting new
                        if ($updated > 0) {
                            continue;
                        }
                    }

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

                $previousTest = PreviousTest::create([
                    'user_id' => $userId,
                    'type' => $type,
                    'question_type' => $user->app_type,
                    'test_datetime' => $now->format('Y-m-d H:i'),
                    'correct_answers' => $correct,
                    'incorrect_answers' => $inCorrect,
                    'total_attempts' => $total,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $quizRows = [];
                foreach ($quizWiseQuestions as $quizId => $qIds) {
                    $quizRows[] = [
                        'previous_test_id' => $previousTest->id,
                        'quiz_id' => $quizId,
                        'question_ids' => json_encode($qIds),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Bulk insert
                StudentQuizHistory::insert($bulkData);
                PreviousTestQuiz::insert($quizRows);

                return [
                    'inserted_count' => count($bulkData),
                    'user_id' => $userId
                ];
            });

            $result = PreviousTest::with('previousTestQuizes')->where('user_id', $user->id)->where("test_datetime", $now->format('Y-m-d H:i'))->first();

            return response()->json(['data' => $result, 'success' => 'Quiz history saved successfully'], 200);
        } catch (Exception $e) {
            Log::error('Insert failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function previousIncorrect()
    {
        $deviceId = request()->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        $studentQuizHistory = StudentQuizHistory::where(['correct' => 0, 'user_id' => $user->id])->pluck('question_id')->toArray();

        $question = Question::with('quiz', 'translations', 'studentQuizHistories')->whereIn('id', $studentQuizHistory)->where('type', $user->app_type)->get();

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
