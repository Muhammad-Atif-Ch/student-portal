<?php

namespace App\Services\Tts;

use Illuminate\Support\Facades\Cache;

class TtsProgressService
{
    public function getProgress(): array
    {
        $progress = Cache::get('tts_progress', [
            'total' => 0,
            'processed' => 0,
            'completed' => 0,
            'partial' => 0,
            'errored' => 0,
            'skipped' => 0,
            'status' => 'idle',
            'by_language' => [],
            'recent_errors' => [],
        ]);

        $processed = $progress['processed'] ?? $progress['completed'] ?? 0;

        $percentage = $progress['total'] > 0
            ? round(($processed / $progress['total']) * 100, 2)
            : 0;

        return [
            'progress' => $progress,
            'percentage' => max(0, min(100, $percentage)),
        ];
    }
}
