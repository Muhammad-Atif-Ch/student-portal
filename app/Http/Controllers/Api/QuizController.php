<?php

namespace App\Http\Controllers\Api;

use App\Models\Quiz;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateQuestionRequest;
use App\Http\Resources\Api\QuestionResource;
use App\Http\Resources\Api\QuizResource;
use App\Models\Question;
use App\Models\QuestionHistory;
use App\Models\StudentQuizHistory;
use App\Models\User;

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
            $quiz = Quiz::with('questions')->get();

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
            $quiz = Quiz::with('questions')->get();

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

                $questionIds = QuestionHistory::where(['user_id' => $userId, 'quiz_id' => $quizId, 'type' => 'official'])->pluck('question_id');

                $remainingQuestions = Question::whereNotIn('id', $questionIds)
                    ->where('quiz_id', $quizId)
                    ->inRandomOrder()
                    ->limit($quizLimit)
                    ->get();

                // If there are remaining questions, add them to the result
                $remainingCount = $remainingQuestions->count();

                // If the number of remaining questions is less than the limit, fetch additional questions
                if ($remainingCount < $quizLimit) {
                    // Reset history since all questions are used
                    QuestionHistory::where(['user_id' => $userId, 'quiz_id' => $quizId, 'type' => 'official'])->delete();

                    // Fetch additional questions to make up for the limit
                    $additionalQuestions = Question::whereNotIn('id', $remainingQuestions->pluck('id')) // Avoid duplicates
                        ->where('quiz_id', $quizId)
                        ->inRandomOrder()
                        ->limit($quizLimit - $remainingCount)
                        ->get();

                    // Merge both sets of questions
                    $remainingQuestions = $remainingQuestions->merge($additionalQuestions);
                }

                // Bulk insert question history instead of looping
                $historyData = $remainingQuestions->map(function ($question) use ($userId, $quizId) {
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

                return [
                    'quiz' => $quiz,
                    'questions' => $remainingQuestions
                ];
            });
        //dd($quizzes);
        // $quiz = Quiz::with([
        //     'questions' => function ($query) use ($remainingQuestions) {
        //         $query->whereIn('id', $remainingQuestions->pluck('id')->toArray());
        //     }
        // ])->find($quizId);

        return new QuestionResource($quizzes);

    }

    public function store(CreateQuestionRequest $request)
    {
        try {
            $data = $request->validated();
            $deviceId = $request->header('Device-Id');

            $userId = User::where('device_id', $deviceId)->first()->id;

            $studentQuizHistory = StudentQuizHistory::create([
                'user_id' => $userId,
                'quiz_id' => $data['quiz_id'],
                'question_id' => $data['question_id'],
                'answer' => $data['answer'],
                'correct' => $data['correct'],
                'type' => $data['type'],
            ]);
            return (new QuestionResource($studentQuizHistory))->additional(['success' => 'Question add successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
