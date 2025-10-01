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

    return <<<SSML
            <speak version='1.0' xml:lang='{$voice['locale']}'>
                <voice xml:lang='{$voice['locale']}' xml:gender='{$voice['gender']}' name='{$voice['name']}'>
                    $text
                </voice>
            </speak>
            SSML;
  }

  private function getVoiceForLanguage($language): array
  {
    // This is a simplified version. You might want to create a more comprehensive mapping
    // or fetch this dynamically from Azure's voice list API
    //dd($language->toArray());
    // $voices = [
    //   'en' => [
    //     'female' => ['locale' => 'en-US', 'gender' => 'Female', 'name' => 'en-US-JennyNeural'],
    //     'male' => ['locale' => 'en-US', 'gender' => 'Male', 'name' => 'en-US-GuyNeural']
    //   ],
    //   'es' => [
    //     'female' => ['locale' => 'es-ES', 'gender' => 'Female', 'name' => 'es-ES-ElviraNeural'],
    //     'male' => ['locale' => 'es-ES', 'gender' => 'Male', 'name' => 'es-ES-AlvaroNeural']
    //   ],
    //   'fr' => [
    //     'female' => ['locale' => 'fr-FR', 'gender' => 'Female', 'name' => 'fr-FR-DeniseNeural'],
    //     'male' => ['locale' => 'fr-FR', 'gender' => 'Male', 'name' => 'fr-FR-HenriNeural']
    //   ],
    //   // Add more language mappings as needed
    //   'sq' => [
    //     'female' => ['locale' => 'sq-AL', 'gender' => 'Female', 'name' => 'sq-AL-AnilaNeural'],
    //     'male' => ['locale' => 'sq-AL', 'gender' => 'Male', 'name' => 'sq-AL-IlirNeural']
    //   ],
    //   'am' => [
    //     'female' => ['locale' => 'am-ET', 'gender' => 'Female', 'name' => 'am-ET-MekdesNeural'],
    //     'male' => ['locale' => 'am-ET', 'gender' => 'Male', 'name' => 'am-ET-AmehaNeural']
    //   ],
    //   'ar' => [
    //     'female' => ['locale' => 'ar-SA', 'gender' => 'Female', 'name' => 'ar-SA-ZariyahNeural'],
    //     'male' => ['locale' => 'ar-SA', 'gender' => 'Male', 'name' => 'ar-SA-HamedNeural']
    //   ],
    //   'hy' => [
    //     'female' => ['locale' => 'hy-AM', 'gender' => 'Female', 'name' => 'hy-AM-AnahitNeural'],
    //     'male' => ['locale' => 'hy-AM', 'gender' => 'Male', 'name' => 'hy-AM-HaykNeural']
    //   ],
    //   'as' => [
    //     'female' => ['locale' => 'as-IN', 'gender' => 'Female', 'name' => 'as-IN-YashicaNeural'],
    //     'male' => ['locale' => 'as-IN', 'gender' => 'Male', 'name' => 'as-IN-PriyomNeural']
    //   ],
    //   'bn' => [
    //     'female' => ['locale' => 'bn-BD', 'gender' => 'Female', 'name' => 'bn-BD-NabanitaNeural'],
    //     'male' => ['locale' => 'bn-BD', 'gender' => 'Male', 'name' => 'bn-BD-PradeepNeural']
    //   ],
    //   'bg' => [
    //     'female' => ['locale' => 'bg-BG', 'gender' => 'Female', 'name' => 'bg-BG-KalinaNeural'],
    //     'male' => ['locale' => 'bg-BG', 'gender' => 'Male', 'name' => 'bg-BG-BorislavNeural']
    //   ],
    //   'my' => [
    //     'female' => ['locale' => 'my-MM', 'gender' => 'Female', 'name' => 'my-MM-NilarNeural'],
    //     'male' => ['locale' => 'my-MM', 'gender' => 'Male', 'name' => 'my-MM-ThihaNeural']
    //   ],
    //   'ca' => [
    //     'female' => ['locale' => 'ca-ES', 'gender' => 'Female', 'name' => 'ca-ES-JoanaNeural'],
    //     'male' => ['locale' => 'ca-ES', 'gender' => 'Male', 'name' => 'ca-ES-EnricNeural']
    //   ],
    //   'zh' => [
    //     'female' => ['locale' => 'zh-CN', 'gender' => 'Female', 'name' => 'zh-CN-XiaoxiaoNeural'],
    //     'male' => ['locale' => 'zh-CN', 'gender' => 'Male', 'name' => 'zh-CN-YunxiNeural']
    //   ],
    //   'hr' => [
    //     'female' => ['locale' => 'hr-HR', 'gender' => 'Female', 'name' => 'hr-HR-GabrijelaNeural'],
    //     'male' => ['locale' => 'hr-HR', 'gender' => 'Male', 'name' => 'hr-HR-SreckoNeural']
    //   ],
    //   'cs' => [
    //     'female' => ['locale' => 'cs-CZ', 'gender' => 'Female', 'name' => 'cs-CZ-VlastaNeural'],
    //     'male' => ['locale' => 'cs-CZ', 'gender' => 'Male', 'name' => 'cs-CZ-AntoninNeural']
    //   ],
    //   'da' => [
    //     'female' => ['locale' => 'da-DK', 'gender' => 'Female', 'name' => 'da-DK-ChristelNeural'],
    //     'male' => ['locale' => 'da-DK', 'gender' => 'Male', 'name' => 'da-DK-JeppeNeural']
    //   ],
    //   'nl' => [
    //     'female' => ['locale' => 'nl-NL', 'gender' => 'Female', 'name' => 'nl-NL-ColetteNeural'],
    //     'male' => ['locale' => 'nl-NL', 'gender' => 'Male', 'name' => 'nl-NL-MaartenNeural']
    //   ],
    //   'fil' => [
    //     'female' => ['locale' => 'fil-PH', 'gender' => 'Female', 'name' => 'fil-PH-BlessicaNeural'],
    //     'male' => ['locale' => 'fil-PH', 'gender' => 'Male', 'name' => 'fil-PH-AngeloNeural']
    //   ],
    //   'fi' => [
    //     'female' => ['locale' => 'fi-FI', 'gender' => 'Female', 'name' => 'fi-FI-SelmaNeural'],
    //     'male' => ['locale' => 'fi-FI', 'gender' => 'Male', 'name' => 'fi-FI-HarriNeural']
    //   ],
    //   'ka' => [
    //     'female' => ['locale' => 'ka-GE', 'gender' => 'Female', 'name' => 'ka-GE-EkaNeural'],
    //     'male' => ['locale' => 'ka-GE', 'gender' => 'Male', 'name' => 'ka-GE-GiorgiNeural']
    //   ],
    //   'de' => [
    //     'female' => ['locale' => 'de-DE', 'gender' => 'Female', 'name' => 'de-DE-LouisaNeural'],
    //     'male' => ['locale' => 'de-DE', 'gender' => 'Male', 'name' => 'de-DE-RalfNeural']
    //   ],
    //   'hu' => [
    //     'female' => ['locale' => 'hu-HU', 'gender' => 'Female', 'name' => 'hu-HU-NoemiNeural'],
    //     'male' => ['locale' => 'hu-HU', 'gender' => 'Male', 'name' => 'hu-HU-TamasNeural']
    //   ],
    //   'id' => [
    //     'female' => ['locale' => 'id-ID', 'gender' => 'Female', 'name' => 'id-ID-GadisNeural'],
    //     'male' => ['locale' => 'id-ID', 'gender' => 'Male', 'name' => 'id-ID-ArdiNeural']
    //   ],
    //   'it' => [
    //     'female' => ['locale' => 'it-IT', 'gender' => 'Female', 'name' => 'it-IT-IsabellaNeural'],
    //     'male' => ['locale' => 'it-IT', 'gender' => 'Male', 'name' => 'it-IT-DiegoNeural']
    //   ],
    //   'lv' => [
    //     'female' => ['locale' => 'lv-LV', 'gender' => 'Female', 'name' => 'lv-LV-EveritaNeural'],
    //     'male' => ['locale' => 'lv-LV', 'gender' => 'Male', 'name' => 'lv-LV-NilsNeural']
    //   ],
    //   'lt' => [
    //     'female' => ['locale' => 'lt-LT', 'gender' => 'Female', 'name' => 'lt-LT-OnaNeural'],
    //     'male' => ['locale' => 'lt-LT', 'gender' => 'Male', 'name' => 'lt-LT-LeonasNeural']
    //   ],
    //   'ne' => [
    //     'female' => ['locale' => 'ne-NP', 'gender' => 'Female', 'name' => 'ne-NP-HemkalaNeural'],
    //     'male' => ['locale' => 'ne-NP', 'gender' => 'Male', 'name' => 'ne-NP-SagarNeural']
    //   ],
    //   'ps' => [
    //     'female' => ['locale' => 'ps-AF', 'gender' => 'Female', 'name' => 'ps-AF-LatifaNeural'],
    //     'male' => ['locale' => 'ps-AF', 'gender' => 'Male', 'name' => 'ps-AF-GulNawazNeural']
    //   ],
    //   'fa' => [
    //     'female' => ['locale' => 'fa-IR', 'gender' => 'Female', 'name' => 'fa-IR-DilaraNeural'],
    //     'male' => ['locale' => 'fa-IR', 'gender' => 'Male', 'name' => 'fa-IR-FaridNeural']
    //   ],
    //   'pl' => [
    //     'female' => ['locale' => 'pl-PL', 'gender' => 'Female', 'name' => 'pl-PL-AgnieszkaNeural'],
    //     'male' => ['locale' => 'pl-PL', 'gender' => 'Male', 'name' => 'pl-PL-MarekNeural']
    //   ],
    //   'pt' => [
    //     'female' => ['locale' => 'pt-PT', 'gender' => 'Female', 'name' => 'pt-PT-RaquelNeural'],
    //     'male' => ['locale' => 'pt-PT', 'gender' => 'Male', 'name' => 'pt-PT-DuarteNeural']
    //   ],
    //   'pa' => [
    //     'female' => ['locale' => 'pa-IN', 'gender' => 'Female', 'name' => 'pa-IN-VaaniNeural'],
    //     'male' => ['locale' => 'pa-IN', 'gender' => 'Male', 'name' => 'pa-IN-OjasNeural']
    //   ],
    //   'ro' => [
    //     'female' => ['locale' => 'ro-RO', 'gender' => 'Female', 'name' => 'ro-RO-AlinaNeural'],
    //     'male' => ['locale' => 'ro-RO', 'gender' => 'Male', 'name' => 'ro-RO-EmilNeural']
    //   ],
    //   'ru' => [
    //     'female' => ['locale' => 'ru-RU', 'gender' => 'Female', 'name' => 'ru-RU-SvetlanaNeural'],
    //     'male' => ['locale' => 'ru-RU', 'gender' => 'Male', 'name' => 'ru-RU-DmitryNeural']
    //   ],
    //   'si' => [
    //     'female' => ['locale' => 'si-LK', 'gender' => 'Female', 'name' => 'si-LK-ThiliniNeural'],
    //     'male' => ['locale' => 'si-LK', 'gender' => 'Male', 'name' => 'si-LK-SameeraNeural']
    //   ],
    //   'so' => [
    //     'female' => ['locale' => 'so-SO', 'gender' => 'Female', 'name' => 'so-SO-UbaxNeural'],
    //     'male' => ['locale' => 'so-SO', 'gender' => 'Male', 'name' => 'so-SO-MuuseNeural']
    //   ],
    //   'sw' => [
    //     'female' => ['locale' => 'sw-TZ', 'gender' => 'Female', 'name' => 'sw-TZ-RehemaNeural'],
    //     'male' => ['locale' => 'sw-TZ', 'gender' => 'Male', 'name' => 'sw-TZ-DaudiNeural']
    //   ],
    //   'th' => [
    //     'female' => ['locale' => 'th-TH', 'gender' => 'Female', 'name' => 'th-TH-PremwadeeNeural'],
    //     'male' => ['locale' => 'th-TH', 'gender' => 'Male', 'name' => 'th-TH-NiwatNeural']
    //   ],
    //   'tr' => [
    //     'female' => ['locale' => 'tr-TR', 'gender' => 'Female', 'name' => 'tr-TR-EmelNeural'],
    //     'male' => ['locale' => 'tr-TR', 'gender' => 'Male', 'name' => 'tr-TR-AhmetNeural']
    //   ],
    //   'uk' => [
    //     'female' => ['locale' => 'uk-UA', 'gender' => 'Female', 'name' => 'uk-UA-PolinaNeural'],
    //     'male' => ['locale' => 'uk-UA', 'gender' => 'Male', 'name' => 'uk-UA-OstapNeural']
    //   ],
    //   'ur' => [
    //     'female' => ['locale' => 'ur-PK', 'gender' => 'Female', 'name' => 'ur-PK-UzmaNeural'],
    //     'male' => ['locale' => 'ur-PK', 'gender' => 'Male', 'name' => 'ur-PK-AsadNeural']
    //   ],
    //   'vi' => [
    //     'female' => ['locale' => 'vi-VN', 'gender' => 'Female', 'name' => 'vi-VN-HoaiMyNeural'],
    //     'male' => ['locale' => 'vi-VN', 'gender' => 'Male', 'name' => 'vi-VN-NamMinhNeural']
    //   ],
    //   'zu' => [
    //     'female' => ['locale' => 'zu-ZA', 'gender' => 'Female', 'name' => 'zu-ZA-ThandoNeural'],
    //     'male' => ['locale' => 'zu-ZA', 'gender' => 'Male', 'name' => 'zu-ZA-ThembaNeural']
    //   ],
    // ];


    // Default to female voice if language not found
    $defaultVoice = ['locale' => 'en-US', 'gender' => 'Female', 'name' => 'en-US-JennyNeural'];

    if (!$language->voices || $language->voices->isEmpty()) {
      Log::warning("No voices available for language", [
        'language_code' => $language->code,
        'language_name' => $language->name
      ]);
      return $defaultVoice;
    }

    // // Check if language exists in voices array
    // if (!isset($voices[$language])) {
    //   return $defaultVoice;
    // }

    // Get voice preference from settings or default to female
    $preferredGender = strtolower(config('tts.preferred_gender', 'female'));

    // $preferredVoice = $language->voices->first(function ($voice) use ($preferredGender) {
    //   return strtolower($voice['gender']) === $preferredGender;
    // })->toArray();

    // return $preferredVoice ?? $defaultVoice;

    $preferredVoiceModel = $language->voices->first(function ($voice) use ($preferredGender) {
      return strtolower($voice['gender']) === $preferredGender;
    });

    $preferredVoice = $preferredVoiceModel ? $preferredVoiceModel->toArray() : null;

    return $preferredVoice ?? $defaultVoice;
  }
}