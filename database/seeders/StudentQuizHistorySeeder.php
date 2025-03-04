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
        $quizzes = Quiz::with('questions')->get();
        $historyRecords = [];

        foreach ($quizzes as $quiz) {
            foreach ($quiz->questions as $question) {
                $historyRecords[] = [
                    'user_id' => 3, // Random student ID
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
