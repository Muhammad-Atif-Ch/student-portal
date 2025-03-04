<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => random_int(1, 5),
            'question' => fake()->sentence(),
            'a' => fake()->sentence(),
            'b' => fake()->sentence(),
            'c' => fake()->sentence(),
            'd' => fake()->sentence(),
            'answer_explanation' => fake()->sentence(),
            'type' => fake()->randomElement(['car', 'bike', 'both']),
            'correct_answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
        ];
    }
}
