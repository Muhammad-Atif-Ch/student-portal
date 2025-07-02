<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LanguageSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Check if CSV file exists
    $csvPath = database_path('languages.csv');

    if (!File::exists($csvPath)) {
      $this->command->error('CSV file not found: ' . $csvPath);
      return;
    }

    // Read CSV file
    $csvData = array_map('str_getcsv', file($csvPath));
    $headers = array_shift($csvData); // Remove header row

    $this->command->info('Importing languages from CSV...');

    foreach ($csvData as $row) {
      if (count($row) >= 3) {
        Language::updateOrCreate(
          ['code' => $row[3]],
          [
            'family' => $row[0] ?? null,
            'name' => $row[1] ?? null,
            'native_name' => $row[2] ?? null,
            'code' => $row[3] ?? null,
            'code_2' => $row[4] ?? null,
          ]
        );
      }
    }

    $this->command->info('Languages imported successfully!');
  }
}