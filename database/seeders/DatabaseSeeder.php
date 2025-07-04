<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            QuestionSeeder::class,
            // StudentQuizHistorySeeder::class,
        ]);
    }
}
