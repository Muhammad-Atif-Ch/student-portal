<?php

namespace App\Services\Tts;

use App\Helpers\ResponseCode;
use App\Models\QuestionTranslation;
use App\Responses\TtsResponse;
use App\Services\AzureTextToSpeech\AzureTTSService;
use Illuminate\Support\Facades\Log;

class TtsActionService
{
    public const FIELDS = ['question', 'a', 'b', 'c', 'd', 'answer_explanation'];

    public function __construct(private TtsResponse $response) {}

    public function reconvertField(QuestionTranslation $translation, string $field, AzureTTSService $tts): TtsResponse
    {
        $translation->loadMissing('language.voices');
        $language = $translation->language;

        if (! $language) {
            $this->response->setResponse(ResponseCode::ERROR, 404, 'Language not found.');

            return $this->response;
        }

        $text = $translation->{"{$field}_translation"};

        if (empty($text)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'Translation text for this field is empty. Translate it first.');

            return $this->response;
        }

        $audioContent = $tts->convertToSpeech($text, $language);

        if (is_array($audioContent) && ($audioContent['status'] ?? false) === false) {
            $this->response->setResponse(ResponseCode::ERROR, 500, $audioContent['message'] ?? 'TTS conversion failed. Check logs for details.');

            return $this->response;
        }

        $audioField = "{$field}_audio";
        $oldFile = $translation->{$audioField};
        if ($oldFile) {
            $oldPath = public_path('audios/'.$oldFile);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $fileName = "{$field}_".time().'_'.$translation->id.'.mp3';
        file_put_contents(public_path('audios/'.$fileName), $audioContent);

        $translation->update([$audioField => $fileName]);

        $this->response->setResponse(ResponseCode::SUCCESS, 200, $this->response->getReconvertResponseMessage(), [
            'field' => $field,
            'audio' => $fileName,
            'audio_url' => asset('audios/'.$fileName),
        ]);

        return $this->response;
    }
}