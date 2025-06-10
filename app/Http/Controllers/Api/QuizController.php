<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Quiz;
use App\Models\User;
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

        if ($quiz == 'all') {
            $quiz = Quiz::with('questions')->paginate(40);

            return QuestionResource::collection($quiz);
        } elseif ($quiz !== null) {
            // Fetch the IDs of questions the student has already taken
            $quiz = Quiz::with('questions')->where('id', $quiz)->first();

            return new QuestionResource($quiz);
        } else {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    }

    public function getPracticeQuestion(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $quiz = $request->quiz;
        $limit = $request->limit ?? 20;

        $userId = User::where('device_id', $deviceId)->first()->id;
        if ($quiz == 'all') {
            $quiz = Quiz::with('questions')->paginate(40);

            return QuestionResource::collection($quiz);
        } elseif ($quiz !== null) {
            // Fetch the IDs of questions the student has already taken
            $questionIds = QuestionHistory::where(['user_id' => $userId, 'quiz_id' => $quiz, 'type' => 'practice'])->pluck('question_id');

            $remainingQuestions = Question::whereNotIn('id', $questionIds)
                ->where('quiz_id', $quiz)
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            // If there are remaining questions, add them to the result
            $remainingCount = $remainingQuestions->count();

            // If the number of remaining questions is less than the limit, fetch additional questions
            if ($remainingCount < $limit) {
                // Reset history since all questions are used
                QuestionHistory::where(['user_id' => $userId, 'quiz_id' => $quiz, 'type' => 'practice'])->delete();

                // Fetch additional questions to make up for the limit
                $additionalQuestions = Question::whereNotIn('id', $remainingQuestions->pluck('id')) // Avoid duplicates
                    ->where('quiz_id', $quiz)
                    ->inRandomOrder()
                    ->limit($limit - $remainingCount)
                    ->get();

                // Merge both sets of questions
                $remainingQuestions = $remainingQuestions->merge($additionalQuestions);
            }

            // Bulk insert question history instead of looping
            $historyData = $remainingQuestions->map(function ($question) use ($userId, $quiz) {
                return [
                    'user_id' => $userId,
                    'quiz_id' => $quiz,
                    'question_id' => $question->id,
                    'type' => 'practice',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            QuestionHistory::insert($historyData);

            $quiz = Quiz::with([
                'questions' => function ($query) use ($remainingQuestions) {
                    $query->whereIn('id', $remainingQuestions->pluck('id')->toArray());
                }
            ])->find($quiz);

            return new QuestionResource($quiz);
        } else {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    }

    public function getOfficialQuestion(Request $request)
    {
        $deviceId = $request->header('Device-Id');

        $userId = User::where('device_id', $deviceId)->first()->id;

        $quizzes = Quiz::select('id', 'title', 'official_test_question')
            ->get()
            ->map(function ($quiz) use ($userId) {
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

            return response()->json(['data' => $result, 'success' => 'Quiz history saved successfully'], 200);
        } catch (Exception $e) {
            Log::error('Insert failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function previousIncorrect()
    {
        $studentQuizHistory = StudentQuizHistory::where('correct', 0)->pluck('question_id')->toArray();

        $question = Question::whereIn('id', $studentQuizHistory)->get();

        return QuestionResource::collection($question);
    }
}
