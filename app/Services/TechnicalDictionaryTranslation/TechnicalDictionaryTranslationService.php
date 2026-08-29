<?php

namespace App\Services\TechnicalDictionaryTranslation;

use App\Helpers\ResponseCode;
use App\Models\Language;
use App\Models\Setting;
use App\Models\TechnicalDictionary;
use App\Models\TechnicalDictionaryTranslation;
use App\Responses\TechnicalDictionaryResponse;
use App\Services\AzureTextToSpeech\AzureTTSService;
use App\Services\AzureTranslation\AzureTranslatorService;
use Illuminate\Support\Facades\File;

class TechnicalDictionaryTranslationService
{
    public function __construct(private TechnicalDictionaryResponse $response) {}

    /**
     * Re-translate the explanation and regenerate its audio for the given language in one action.
     */
    public function regenerate(
        TechnicalDictionary $dictionary,
        Language $language,
        AzureTranslatorService $translator,
        AzureTTSService $tts,
    ): TechnicalDictionaryResponse {
        if (empty(Setting::translationApiKey())) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translator API key is not configured. Set it in Admin > App Settings > API Settings.');

            return $this->response;
        }

        if (blank($dictionary->explanation)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'This term has no explanation to translate.');

            return $this->response;
        }

        $translation = TechnicalDictionaryTranslation::firstOrCreate([
            'technical_dictionary_id' => $dictionary->id,
            'language_id' => $language->id,
        ]);

        try {
            $translated = $translator->translateOne($dictionary->explanation, $language->code);
            $translation->update(['explanation_translation' => $translated]);
        } catch (\Throwable $e) {
            $this->response->setResponse(ResponseCode::ERROR, 500, $e->getMessage());

            return $this->response;
        }

        $language->loadMissing('voices');

        $audioContent = $tts->convertToSpeech($translated, $language);

        if (is_array($audioContent)) {
            $translation->update(['status' => 'partial']);

            $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Translated successfully, but audio generation failed.', [
                'translation' => $translated,
                'audio_url' => $translation->explanation_audio_url,
            ]);

            return $this->response;
        }

        $directory = public_path('audios/technical-dictionary');
        File::ensureDirectoryExists($directory);

        if ($translation->explanation_audio) {
            $oldPath = "{$directory}/{$translation->explanation_audio}";

            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $fileName = "explanation_{$dictionary->id}_{$language->code}_".time().'.mp3';
        file_put_contents("{$directory}/{$fileName}", $audioContent);

        $translation->update([
            'explanation_audio' => $fileName,
            'status' => 'completed',
        ]);

        $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Translation and audio regenerated successfully.', [
            'translation' => $translated,
            'audio_url' => asset("audios/technical-dictionary/{$fileName}"),
        ]);

        return $this->response;
    }
}
