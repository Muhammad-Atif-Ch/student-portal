<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Question::create([
            'quiz_id' => 1,
            'question' => 'What is the capital of France?',
            'a' => 'Paris  is the capital of France',
            'b' => 'London is the capital of England',
            'c' => 'Berlin is the capital of Germany',
            'd' => 'Madrid is the capital of Spain',
            'type' => 'car',
            'answer_explanation' => 'Paris is the capital of France',
            'correct_answer' => 'a',
        ]);
        // Question::create([
        //     'question_translation' => 'What is the capital of France?',
        //     'a_translation' => 'Paris',
        //     'b_translation' => 'London',
        //     'c_translation' => 'Berlin',
        //     'd_translation' => 'Madrid',
        //     'type' => 'car',
        //     'answer_explanation_translation' => 'Paris is the capital of France',
        //     'correct_answer' => 'a',
        // ]);
        // Question::factory()->count(1)->create();
    }
}
