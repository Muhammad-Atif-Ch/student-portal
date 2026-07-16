<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Cache;

class TranslationProgressService
{
    public function getProgress(): array
    {
        return $this->build('translation_progress');
    }

    public function getQuestionProgress(int $questionId): array
    {
        $progress = Cache::get("translation_progress_question_{$questionId}", [
            'total' => 0, 'completed' => 0, 'status' => 'idle', 'message' => '', 'question_id' => $questionId,
        ]);

        $percentage = $progress['total'] > 0 ? round(($progress['completed'] / $progress['total']) * 100, 2) : 0;

        return ['progress' => $progress, 'percentage' => max(0, min(100, $percentage))];
    }

    private function build(string $key): array
    {
        $progress = Cache::get($key, [
            'total' => 0, 'processed' => 0, 'completed' => 0, 'partial' => 0,
            'errored' => 0, 'skipped' => 0, 'status' => 'idle', 'by_language' => [], 'recent_errors' => [],
        ]);

        $processed = $progress['processed'] ?? $progress['completed'] ?? 0;
        $percentage = $progress['total'] > 0 ? round(($processed / $progress['total']) * 100, 2) : 0;

        return ['progress' => $progress, 'percentage' => max(0, min(100, $percentage))];
    }
}
