<?php

namespace App\Services\Translation;

use App\Helpers\ResponseCode;
use App\Jobs\BulkTranslateAndSpeakQuestionsJob;
use App\Responses\TranslationResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CombinedActionService
{
    public function __construct(private TranslationResponse $response) {}

    public function startAll(): TranslationResponse
    {
        if (empty(config('services.azure_translator.key'))) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translator API key is not configured.');
            return $this->response;
        }

        Cache::forget('combined_stop_flag');
        Cache::forget('combined_progress');

        try {
            BulkTranslateAndSpeakQuestionsJob::dispatch();
            $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Translation + voice generation started.');
        } catch (\Exception $e) {
            Log::error('Combined start error: '.$e->getMessage());
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Failed to start.');
        }

        return $this->response;
    }

    public function stop(): TranslationResponse
    {
        Cache::put('combined_stop_flag', true, 3600);
        $current = Cache::get('combined_progress', []);
        Cache::put('combined_progress', array_merge($current, ['status' => 'stopped', 'message' => 'Stopped by user']), 3600);
        $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Stopped successfully.');
        return $this->response;
    }

    public function progress(): array
    {
        $progress = Cache::get('combined_progress', ['total' => 0, 'processed' => 0, 'status' => 'idle', 'by_language' => [], 'recent_errors' => []]);
        $processed = $progress['processed'] ?? 0;
        $pct = $progress['total'] > 0 ? round(($processed / $progress['total']) * 100, 2) : 0;
        return ['progress' => $progress, 'percentage' => max(0, min(100, $pct))];
    }
}