<?php

namespace App\Services\Tts;

use App\Helpers\ResponseCode;
use App\Jobs\TextToSpeechConversionJob;
use App\Models\QuestionTranslation;
use App\Models\Setting;
use App\Responses\TtsResponse;
use App\Services\AzureTextToSpeech\AzureTTSService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TtsActionService
{
    public const FIELDS = ['question', 'a', 'b', 'c', 'd', 'answer_explanation'];

    public function __construct(private TtsResponse $response) {}

    public function convertAll(): TtsResponse
    {
        try {
            $this->resetFlags('tts');
            TextToSpeechConversionJob::dispatch();
            $this->response->setResponse(ResponseCode::SUCCESS, 200, $this->response->getCreateResponseMessage());
        } catch (\Exception $e) {
            Log::error('TTS conversion error: '.$e->getMessage());
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Failed to start audio conversion');
        }

        return $this->response;
    }

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

    public function stopConversion(): TtsResponse
    {
        try {
            Setting::first()?->update(['tts_stopped' => true]);

            $current = Cache::get('tts_progress', []);
            Cache::put('tts_progress', array_merge($current, [
                'status' => 'stopped',
                'message' => 'Audio conversion process stopped by user',
            ]), 3600);

            Cache::put('tts_stop_flag', true, 3600);

            $this->response->setResponse(ResponseCode::SUCCESS, 200, $this->response->getStopResponseMessage());
        } catch (\Exception $e) {
            Log::error('Stop TTS conversion error: '.$e->getMessage());
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Failed to stop audio conversion');
        }

        return $this->response;
    }

    private function resetFlags(string $prefix): void
    {
        DB::transaction(function () use ($prefix) {
            Setting::first()?->update(["{$prefix}_stopped" => false]);
            Cache::forget("{$prefix}_stop_flag");
            Cache::forget("{$prefix}_progress");
        });
    }
}