<?php

namespace App\Http\Controllers\Api;


use Throwable;
use Carbon\Carbon;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Question;
use App\Models\ExamType;
use App\Models\PreviousTest;
use Illuminate\Http\Request;
use App\Models\ExamPoolRule;
use App\Models\QuestionHistory;
use App\Models\PreviousTestQuiz;
use App\Models\StudentQuizHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\QuestionResource;
use App\Http\Requests\Api\CreateQuestionRequest;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


class TestController extends Controller
{
    public function getPracticeQuestion(Request $request)
    {
        return $this->buildExamResponse($request, 'practice');
    }

    public function getOfficialQuestion(Request $request)
    {
        return $this->buildExamResponse($request, 'official');
    }

    private function buildExamResponse(Request $request, string $historyType)
    {
        $request->validate([
            'exam_type_id' => ['required', 'integer', 'exists:exam_types,id'],
        ]);

        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->firstOrFail();

        $examType = ExamType::with(['poolRules', 'targetTypes'])->findOrFail($request->exam_type_id);
        $targetTypes = $examType->targetTypes->pluck('type')->all();
        $quizIds = $examType->poolRules->pluck('quiz_id')->unique();

        $quizzes = Quiz::select('id', 'title')
            ->whereIn('id', $quizIds)
            ->get()
            ->map(function ($quiz) use ($examType, $targetTypes, $user, $historyType) {
                $quizId = $quiz->id;
                $rules = $examType->poolRules->where('quiz_id', $quizId);

                $questions = collect();
                foreach ($rules as $rule) {
                    $questions = $questions->merge(
                        $this->fetchRuleQuestions($quizId, $rule, $targetTypes, $user->id, $historyType)
                    );
                }

                $historyData = $questions->map(function ($question) use ($user, $quizId, $historyType) {
                    return [
                        'user_id' => $user->id,
                        'quiz_id' => $quizId,
                        'question_id' => $question->id,
                        'type' => $historyType,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();

                QuestionHistory::insert($historyData);

                $quiz->setRelation('questions', $questions);

                return $quiz;
            });

        return new QuestionResource([
            'exam_type' => $examType->only(['id', 'name', 'total_questions', 'passing_marks', 'total_time_minutes']),
            'quizzes' => $quizzes,
        ]);
    }

    /**
     * Scope a Question query to a pool rule's common/specific vehicle-type pool.
     */
    private function applyPoolRuleFilter($query, ExamPoolRule $rule, array $targetTypes)
    {
        if ($rule->pool_type === 'specific') {
            $query->whereHas('type', fn($q) => $q->where('type', $rule->specific_type))
                ->has('type', '=', 1);
        } else {
            $query->whereHas('type', fn($q) => $q->whereIn('type', $targetTypes))
                ->has('type', '>=', 2);
        }

        return $query;
    }

    /**
     * Fetch required_count questions for one pool rule, preferring previously
     * wrong-answered questions first, then new ones, resetting history if the pool runs out.
     */
    private function fetchRuleQuestions(int $quizId, ExamPoolRule $rule, array $targetTypes, int $userId, string $historyType): Collection
    {
        $limit = $rule->required_count;

        $baseQuery = fn() => $this->applyPoolRuleFilter(
            Question::with(['translations', 'type'])->where('quiz_id', $quizId),
            $rule,
            $targetTypes
        );

        $wrongAttemptedQuestionIds = StudentQuizHistory::where([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'type' => $historyType,
            'correct' => 0,
        ])->pluck('question_id');

        $wrongQuestions = $baseQuery()
            ->whereIn('id', $wrongAttemptedQuestionIds)
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        $remainingLimit = $limit - $wrongQuestions->count();
        $remainingQuestions = collect();


        if ($remainingLimit > 0) {
            $alreadyAttemptedIds = QuestionHistory::where([
                'user_id' => $userId,
                'quiz_id' => $quizId,
                'type' => $historyType,
            ])->pluck('question_id');

            $remainingQuestions = $baseQuery()
                ->whereNotIn('id', $alreadyAttemptedIds)
                ->whereNotIn('id', $wrongQuestions->pluck('id'))
                ->inRandomOrder()
                ->limit($remainingLimit)
                ->get();

            $actualRemainingCount = $remainingQuestions->count();

            if ($actualRemainingCount < $remainingLimit) {
                QuestionHistory::where([
                    'user_id' => $userId,
                    'quiz_id' => $quizId,
                    'type' => $historyType,
                ])->delete();

                $needed = $remainingLimit - $actualRemainingCount;

                $extraQuestions = $baseQuery()
                    ->whereNotIn('id', $wrongQuestions->pluck('id'))
                    ->whereNotIn('id', $remainingQuestions->pluck('id'))
                    ->inRandomOrder()
                    ->limit($needed)
                    ->get();

                $remainingQuestions = $remainingQuestions->merge($extraQuestions);
            }
        }

        $result = $wrongQuestions->merge($remainingQuestions)->take($limit);

        // If a "specific" pool can't fill required_count, top up from the "common" pool.
        if ($rule->pool_type === 'specific' && $result->count() < $limit) {
            $shortfall = $limit - $result->count();

            $commonQuestions = $this->applyPoolRuleFilter(
                Question::with(['translations', 'type'])->where('quiz_id', $quizId),
                new ExamPoolRule(['pool_type' => 'common']),
                $targetTypes
            )
                ->whereNotIn('id', $result->pluck('id'))
                ->inRandomOrder()
                ->limit($shortfall)
                ->get();

            $result = $result->merge($commonQuestions);
        }

        return $result;
    }

    public function store(CreateQuestionRequest $request)
    {
        try {
            $deviceId = $request->header('Device-Id');
            $user = User::where('device_id', $deviceId)->firstOrFail();

            $validatedData = $request->validated();
            $now = Carbon::now();

            // Use database transaction for data integrity
            $result = DB::transaction(function () use ($validatedData, $user, $now) {
                $correct = 0;
                $inCorrect = 0;
                $total = 0;
                $type = "";
                $bulkData = [];
                $pretestBulkData = [];
                $userId = $user->id;

                foreach ($validatedData['data'] as $item) {
                    $total++;
                    $type = $item['type'];
                    if ($item['correct'] == 1) {
                        $correct++;
                    } else {
                        $inCorrect++;
                    }

                    $pretestBulkData[] = [
                        'quiz_id' => $item['quiz_id'],
                        'question_id' => $item['question_id'],
                        'answer' => $item['answer'],
                        'correct' => $item['correct'],
                        'type' => $item['type'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
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

                // Add previous_test_id without foreach
                $pretestBulkData = array_map(function ($row) use ($previousTest) {
                    return $row + ['previous_test_id' => $previousTest->id];
                }, $pretestBulkData);

                // Bulk insert
                StudentQuizHistory::insert($bulkData);
                PreviousTestQuiz::insert($pretestBulkData);

                return $previousTest->id;
            });

            $previousTest = PreviousTest::with('previousTestQuizes')->findOrFail($result);

            return response()->json(['data' => $previousTest, 'success' => 'Quiz history saved successfully'], 200);
        } catch (Throwable $e) {
            Log::error('Insert failed: ' . $e->getMessage());
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
