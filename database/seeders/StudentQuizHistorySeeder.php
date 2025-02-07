<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Database\Seeder;
use App\Models\StudentQuizHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StudentQuizHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //StudentQuizHistory::factory()->count(5000)->create();

        // Quiz::factory(10)
        // ->has(Question::factory(5000), 'questions') // Each quiz has 10 questions
        // ->create()
        // ->each(function ($quiz) {
        //     // For each question in the quiz, create StudentQuizHistory
        //     $quiz->questions->each(function ($question) use ($quiz) {
        //         StudentQuizHistory::factory()
        //             ->withQuizAndQuestions($quiz, $question) // Link quiz & question
        //             ->create();
        //     });
        // });

        $quizzes = Quiz::with('questions')->get();
        $historyRecords = [];

        foreach ($quizzes as $quiz) {
            foreach ($quiz->questions as $question) {
                $historyRecords[] = [
                    'student_id' => 3, // Random student ID
                    'quiz_id' => $quiz->id, // Current quiz ID
                    'question_id' => $question->id, // Current question ID
                    'answer' => fake()->randomElement(['a', 'b', 'c', 'd']), // Random answer
                    'correct' => fake()->boolean(true), // Random correctness
                    'type' => fake()->randomElement(['practice', 'official']), // Random type
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert all records in a single query
        StudentQuizHistory::insert($historyRecords);
    }
}
