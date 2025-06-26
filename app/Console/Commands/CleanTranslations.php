<?php

namespace App\Console\Commands;

use App\Models\QuestionTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CleanTranslations extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'translations:clean {--force : Force cleanup without confirmation}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Clean up translation data and stop running processes';

  /**
   * Execute the console command.
   */
  public function handle()
  {
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
    $count = QuestionTranslation::count();

    if ($count === 0) {
      $this->info('No translation records found.');
      return 0;
    }

    $this->warn("Found {$count} translation records.");

    if (!$this->option('force')) {
      if (!$this->confirm("Do you want to delete all {$count} translation records?")) {
        $this->info('Cleanup cancelled.');
        return 0;
      }
    }

    // Delete all translation records
    QuestionTranslation::truncate();

    $this->info("Successfully deleted {$count} translation records.");
    $this->info('Translation data cleaned up successfully.');

    return 0;
  }
}