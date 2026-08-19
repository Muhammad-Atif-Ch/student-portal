<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            QuizSeeder::class,
            SettingSeeder::class,
            LanguageSeeder::class,
            LanguageVoiceSeeder::class,
            QuestionSeeder::class,
            ExamFormulaSeeder::class,
            // StudentQuizHistorySeeder::class,
        ]);
    }
}
