<?php

namespace Database\Seeders;

use App\Models\Quiz;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Quiz::insert([
            [
                'title' => 'Legal Matters/Rules of the Road',
                'official_test_question' => 7,
            ],
            [
                'title' => 'Managing Risk',
                'official_test_question' => 7,
            ],
            [
                'title' => 'Safe and Socially Responsible Driving',
                'official_test_question' => 23,
            ],
            [
                'title' => 'Control of Vehicle',
                'official_test_question' => 2,
            ],
            [
                'title' => 'Technical Matters',
                'official_test_question' => 1,
            ],
        ]);
    }
}
