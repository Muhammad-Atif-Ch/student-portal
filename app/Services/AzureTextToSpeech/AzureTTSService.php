<?php

namespace App\Services\AzureTextToSpeech;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AzureTTSService
{
  private string $apiKey;
  private string $region;
  private string $endpoint;

  public function __construct()
  {
    $settings = Setting::cached();
    $this->apiKey = (string) $settings?->tts_api_key;
    $this->region = (string) $settings?->tts_api_region;
    $this->endpoint = "https://{$this->region}.tts.speech.microsoft.com/cognitiveservices/v1";
  }

  public function convertToSpeech(string $text, $language): string|array
  {
    try {
      // Get voice for language
      $voice = $this->getVoiceForLanguage($language);

      // Prepare SSML
      $ssml = $this->generateSSML($text, $voice);

      $headers = [
        'Ocp-Apim-Subscription-Key' => $this->apiKey,
        'Ocp-Apim-Subscription-Region' => $this->region,
        'X-Microsoft-OutputFormat' => 'audio-16khz-128kbitrate-mono-mp3',
        'User-Agent' => 'QuestionTTS'
      ];

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

      return [
        'status' => false,
        'message' => $response->body(),
      ];
    } catch (\Exception $e) {
      Log::error('Azure TTS Exception', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      return [
        'status' => false,
        'message' => $e->getMessage(),
      ];
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

    // Azure's neural voices aggressively trim silence at line-break/sentence
    // boundaries, which can clip the first word of the next sentence. Turning
    // both raw newlines AND in-line sentence punctuation (., !, ?, and the
    // Urdu/Arabic equivalents ۔ ؟) into explicit <s> sentence tags (instead of
    // leaving them as bare text) stops the engine from mis-detecting the
    // boundary and swallowing the following word.
    //
    // Stacking a manual <break> on top of <mstts:silence type="Sentenceboundary-*">
    // makes the engine over-compensate and clip real speech instead of silence,
    // which is why the previous attempt made things worse and even ate the very
    // first word of the clip. Only the "Leading" silence (the gap before the
    // first word starts) needs to be pinned explicitly; sentence boundaries are
    // left to the single mstts:silence directive below, with no extra <break>.
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $sentences = [];
    foreach ($lines as $line) {
      $parts = preg_split('/(?<=[.!?۔؟])\s+/u', trim($line));
      foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '') {
          $sentences[] = $part;
        }
      }
    }

    $body = implode('', array_map(fn($sentence) => "<s>{$sentence}</s>", $sentences));

    return <<<SSML
            <speak version='1.0' xml:lang='{$voice['locale']}' xmlns:mstts='https://www.w3.org/2001/mstts'>
                <voice xml:lang='{$voice['locale']}' xml:gender='{$voice['gender']}' name='{$voice['name']}'>
                    <mstts:silence type='Leading-exact' value='350ms'/>
                    <mstts:silence type='Sentenceboundary-exact' value='200ms'/>
                    $body
                </voice>
            </speak>
            SSML;
  }

  private function getVoiceForLanguage($language): array
  {
    // Default to female voice if language not found
    $defaultVoice = ['locale' => 'en-US', 'gender' => 'Female', 'name' => 'en-US-JennyNeural'];

    if (!$language->voices || $language->voices->isEmpty()) {
      Log::warning("No voices available for language", [
        'language_code' => $language->code,
        'language_name' => $language->name
      ]);
      return $defaultVoice;
    }

    // Get voice preference from settings or default to female
    $preferredGender = strtolower(config('tts.preferred_gender', 'female'));

    $preferredVoice = optional(
      $language->voices->first(function ($voice) use ($preferredGender) {
        return strtolower($voice['gender']) === $preferredGender;
      })
    )->toArray();
    Log::info('Selected voice for language', $preferredVoice ?: $defaultVoice);
    return $preferredVoice ?: $defaultVoice;
  }
}