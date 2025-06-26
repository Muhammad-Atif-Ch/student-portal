<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestTranslationAPI extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'translation:test-api {api_key}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Test Google Translate API with provided API key';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $apiKey = $this->argument('api_key');

    $this->info('Testing Google Translate API...');

    try {
      $response = Http::get('https://translation.googleapis.com/language/translate/v2', [
        'key' => $apiKey,
        'q' => 'Hello world',
        'target' => 'es',
        'format' => 'text'
      ]);

      $this->info("Response Status: " . $response->status());
      $this->info("Response Body: " . $response->body());

      if ($response->successful()) {
        $data = $response->json();
        $translatedText = $data['data']['translations'][0]['translatedText'] ?? null;
        $this->info("✅ Translation Result: " . $translatedText);
        $this->info("✅ API is working correctly!");
        return 0;
      } else {
        $this->error("❌ API Error: " . $response->body());

        // Parse error details
        $errorData = json_decode($response->body(), true);
        if (isset($errorData['error']['code'])) {
          $errorCode = $errorData['error']['code'];
          $this->error("Error Code: " . $errorCode);

          switch ($errorCode) {
            case 400:
              $this->error("Bad Request - Check your parameters");
              break;
            case 401:
              $this->error("Unauthorized - Invalid API key");
              break;
            case 403:
              $this->error("Forbidden - API key not authorized for Translation API");
              break;
            case 429:
              $this->error("Rate Limit Exceeded - Too many requests");
              break;
            default:
              $this->error("Unknown error code: " . $errorCode);
          }
        }
        return 1;
      }
    } catch (\Exception $e) {
      $this->error("❌ Exception: " . $e->getMessage());
      return 1;
    }
  }
}