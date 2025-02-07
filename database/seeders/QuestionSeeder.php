<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Question::factory()->count(5000)->create();

        $question = [
            'quiz_id' => random_int(1, 10),
            'question' => fake()->sentence(),
            'a' => fake()->sentence(),
            'b' => fake()->sentence(),
            'c' => fake()->sentence(),
            'd' => fake()->sentence(),
            'answer_explanation' => fake()->sentence(),
            'correct_answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
        ];

        Question::insert($question);
    }
}
