<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;
use App\Models\QuestionTranslation;

class ForceStopTranslation extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'translation:force-stop';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Forcefully stop any running translation processes';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('Forcefully stopping translation processes...');

    // Set database stop flag
    $setting = Setting::first();
    if ($setting) {
      $setting->update(['translation_stopped' => true]);
      $this->info('Database stop flag set');
    }

    // Clear all cache
    Cache::flush();
    $this->info('All cache cleared');

    // Set multiple stop flags
    Cache::put('translation_progress', [
      'total' => 0,
      'completed' => 0,
      'current_question' => 0,
      'current_language' => 0,
      'status' => 'stopped',
      'message' => 'Translation process forcefully stopped'
    ], 3600);

    Cache::put('translation_stop_flag', true, 3600);
    $this->info('Stop flags set in cache');

    // Count and optionally delete existing translations
    $count = QuestionTranslation::count();
    if ($count > 0) {
      if ($this->confirm("Found {$count} translation records. Delete them?")) {
        QuestionTranslation::truncate();
        $this->info("Deleted {$count} translation records");
      } else {
        $this->info("Kept {$count} translation records");
      }
    }

    $this->info('Translation processes forcefully stopped!');
    return 0;
  }
}