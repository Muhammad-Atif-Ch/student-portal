<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentQuizHistory>
 */
class StudentQuizHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => 3,
            //'quiz_id' => random_int(1, 10),
            //'question_id' => random_int(1, 5000),
            'answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
            'correct' => fake()->boolean(true),
            'type' => fake()->randomElement(['practice', 'official']),
        ];
    }

    // Custom state to attach quiz and question
    public function withQuizAndQuestions(Quiz $quiz, Question $question)
    {
        return $this->state(function (array $attributes) use ($quiz, $question) {
            return [
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
            ];
        });
    }
}
