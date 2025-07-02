<?php

namespace App\Console\Commands;

use App\Models\Language;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportLanguages extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'languages:import {file?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Import languages from CSV file';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $file = $this->argument('file') ?? 'database/language-Sat Jun 14 2025.csv';
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
        $language = Language::updateOrCreate(
          ['code' => $row[0]],
          [
            'code' => $row[0] ?? null,
            'code_2' => $row[1] ?? null,
            'name' => $row[2] ?? null,
            'status' => 'active'
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
  }
}