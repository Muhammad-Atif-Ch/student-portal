<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lenguage;
use Illuminate\Support\Facades\File;

class LanguageSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Check if CSV file exists
    $csvPath = database_path('lenguage-Sat Jun 14 2025.csv');

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
        Lenguage::updateOrCreate(
          ['code' => $row[0]],
          [
            'code' => $row[0] ?? null,
            'code_2' => $row[1] ?? null,
            'name' => $row[2] ?? null,
            'status' => '0'
          ]
        );
      }
    }

    $this->command->info('Languages imported successfully!');
  }
}