<?php

namespace App\Services\AzureTextToSpeech;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AzureTTSService
{
  private string $apiKey;
  private string $region;
  private string $endpoint;

  public function __construct()
  {
    $this->apiKey = env('AZURE_SPEECH_API_KEY');
    $this->region = env('AZURE_SPEECH_API_REGION');
    $this->endpoint = "https://{$this->region}.tts.speech.microsoft.com/cognitiveservices/v1";
    // $this->endpoint = env('AZURE_SPEECH_API_URL');
  }

  public function convertToSpeech(string $text, string $language): string|false
  {
    try {
      // Get voice for language
      $voice = $this->getVoiceForLanguage($language);

      // Debug log the configuration
      // Log::debug('Azure TTS Configuration', [
      //   'endpoint' => $this->endpoint,
      //   'region' => $this->region,
      //   'language' => $language,
      //   'voice' => $voice,
      //   'has_key' => !empty($this->apiKey)
      // ]);

      // Prepare SSML
      $ssml = $this->generateSSML($text, $voice);

      // // Debug log the SSML
      // Log::debug('Azure TTS SSML Request', [
      //   'ssml' => $ssml
      // ]);

      $headers = [
        'Ocp-Apim-Subscription-Key' => $this->apiKey,
        'Ocp-Apim-Subscription-Region' => $this->region,
        'X-Microsoft-OutputFormat' => 'audio-16khz-128kbitrate-mono-mp3',
        'User-Agent' => 'QuestionTTS'
      ];

      // // Debug log headers (excluding the key)
      // Log::debug('Azure TTS Headers', array_merge(
      //   $headers,
      //   ['Ocp-Apim-Subscription-Key' => '[REDACTED]']
      // ));

      // Make API request
      $response = Http::withHeaders($headers)
        ->withBody($ssml, 'application/ssml+xml')
        ->post($this->endpoint);

      if ($response->successful()) {
        Log::debug('Azure TTS Success', [
          'status' => $response->status(),
          'content_length' => strlen($response->body()),
        ]);
        return $response->body();
      }

      Log::error('Azure TTS Error', [
        'status' => $response->status(),
        'body' => $response->body(),
        'headers' => $response->headers()
      ]);
      return false;
    } catch (\Exception $e) {
      Log::error('Azure TTS Exception', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      return false;
    }
  }

  private function generateSSML(string $text, array $voice): string
  {
    // Clean and validate the text
    $text = trim($text);
    if (empty($text)) {
      Log::warning('Azure TTS: Empty text provided');
      $text = '.'; // Minimum valid SSML requires some text
    }
    $text = htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');

    return <<<SSML
            <speak version='1.0' xml:lang='{$voice['locale']}'>
                <voice xml:lang='{$voice['locale']}' xml:gender='{$voice['gender']}' name='{$voice['name']}'>
                    $text
                </voice>
            </speak>
            SSML;
  }

  private function getVoiceForLanguage(string $language): array
  {
    // This is a simplified version. You might want to create a more comprehensive mapping
    // or fetch this dynamically from Azure's voice list API
    $voices = [
      'en' => ['locale' => 'en-US', 'gender' => 'Female', 'name' => 'en-US-JennyNeural'],
      'es' => ['locale' => 'es-ES', 'gender' => 'Female', 'name' => 'es-ES-ElviraNeural'],
      'fr' => ['locale' => 'fr-FR', 'gender' => 'Female', 'name' => 'fr-FR-DeniseNeural'],
      // Add more language mappings as needed
    ];

    return $voices[$language] ?? $voices['en']; // Default to English if language not found
  }
}