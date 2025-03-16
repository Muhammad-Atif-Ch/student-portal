<?php

namespace Database\Factories;

use Illuminate\Support\Str;
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
            'image' => "demo.jpg",
            'answer_explanation' => fake()->sentence(),
            'extra_explanation' => fake()->sentence(),
            'type' => fake()->randomElement(['car', 'bike', 'both']),
            'correct_answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
        ];
    }

    private function generateFakeImage()
    {
        $folder = public_path('images'); // Define the storage location

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true); // Ensure directory exists
        }

        $imageName = Str::random(10) . '.jpg'; // Unique file name
        $filePath = $folder . '/' . $imageName;

        // Generate and save image
        $imageFullPath = fake()->image($folder, 640, 480, null, false);

        // Rename the generated image (optional)
        rename($folder . '/' . basename($imageFullPath), $filePath);

        return 'images/' . $imageName;
    }
}
