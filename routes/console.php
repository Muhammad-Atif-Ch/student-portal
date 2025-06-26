<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Lenguage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('languages:import {file?}', function ($file = null) {
    $file = $file ?? 'database/lenguage-Sat Jun 14 2025.csv';
    $csvPath = base_path($file);

    if (!File::exists($csvPath)) {
        $this->error('CSV file not found: ' . $csvPath);
        return 1;
    }

    $this->info('Importing languages from: ' . $csvPath);

    // Read CSV file
    $csvData = array_map('str_getcsv', file($csvPath));
    $headers = array_shift($csvData); // Remove header row

    $bar = $this->output->createProgressBar(count($csvData));
    $bar->start();

    $imported = 0;
    $updated = 0;

    foreach ($csvData as $row) {
        if (count($row) >= 3) {
            $language = Lenguage::updateOrCreate(
                ['code' => $row[0]],
                [
                    'code' => $row[0] ?? null,
                    'code_2' => $row[1] ?? null,
                    'name' => $row[2] ?? null,
                    'status' => '0'
                ]
            );

            if ($language->wasRecentlyCreated) {
                $imported++;
            } else {
                $updated++;
            }
        }
        $bar->advance();
    }

    $bar->finish();
    $this->newLine();
    $this->info("Import completed!");
    $this->info("Imported: $imported languages");
    $this->info("Updated: $updated languages");

    return 0;
})->purpose('Import languages from CSV file');

Artisan::command('translations:clean {--force}', function ($force = false) {
    // Stop any running translation process
    Cache::put('translation_progress', [
        'total' => 0,
        'completed' => 0,
        'current_question' => 0,
        'current_language' => 0,
        'status' => 'stopped',
        'message' => 'Translation process stopped by cleanup command'
    ], 3600);

    $this->info('Translation process stopped.');

    // Count existing translations
    $count = \App\Models\QuestionTranslation::count();

    if ($count === 0) {
        $this->info('No translation records found.');
        return 0;
    }

    $this->warn("Found {$count} translation records.");

    if (!$force) {
        if (!$this->confirm("Do you want to delete all {$count} translation records?")) {
            $this->info('Cleanup cancelled.');
            return 0;
        }
    }

    // Delete all translation records
    \App\Models\QuestionTranslation::truncate();

    $this->info("Successfully deleted {$count} translation records.");
    $this->info('Translation data cleaned up successfully.');

    return 0;
})->purpose('Clean up translation data and stop running processes');
