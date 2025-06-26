<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

// Test Google Translate API
function testGoogleTranslateAPI($apiKey)
{
  echo "Testing Google Translate API...\n";

  try {
    $response = Http::get('https://translation.googleapis.com/language/translate/v2', [
      'key' => $apiKey,
      'q' => 'Hello world',
      'target' => 'es',
      'format' => 'text'
    ]);

    echo "Response Status: " . $response->status() . "\n";
    echo "Response Body: " . $response->body() . "\n";

    if ($response->successful()) {
      $data = $response->json();
      $translatedText = $data['data']['translations'][0]['translatedText'] ?? null;
      echo "Translation Result: " . $translatedText . "\n";
      return true;
    } else {
      echo "API Error: " . $response->body() . "\n";
      return false;
    }
  } catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    return false;
  }
}

// Test with your API key
$apiKey = 'AIzaSyDOMw1VSe5fkjWusJnazQ-Zb3RkH4X6Vlw'; // Replace with your actual API key
testGoogleTranslateAPI($apiKey);