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
            ],
            [
                'title' => 'Managing Risk',
            ],
            [
                'title' => 'Safe and Socially Responsible Driving',
            ],
            [
                'title' => 'Control of Vehicle',
            ],
            [
                'title' => 'Technical Matters',
            ],
        ]);
    }
}
