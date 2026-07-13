<?php

namespace App\Jobs;

use App\Models\Language;
use App\Models\QuestionTranslation;
use App\Services\AzureTextToSpeech\AzureTTSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TextToSpeechConversionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const FIELDS = ['question', 'a', 'b', 'c', 'd', 'answer_explanation'];

    private const MAX_RECENT_ERRORS = 200;

    private const PROGRESS_FLUSH_EVERY = 20;

    public int $timeout = 0;

    public int $tries = 1;

    private int $pendingProgressWrites = 0;

    public function handle()
    {
        $languages = Language::where('status', 1)->get();

        $translations = QuestionTranslation::with('language')
            ->whereIn('language_id', $languages->pluck('id'))
            ->orderBy('id')
            ->get();

        $progress = [
            'total' => $translations->count(),
            'processed' => 0,
            'completed' => 0,
            'partial' => 0,
            'errored' => 0,
            'skipped' => 0,
            'status' => 'running',
            'message' => 'Starting audio conversion...',
            'by_language' => $languages->mapWithKeys(fn ($language) => [
                $language->id => [
                    'name' => $language->name,
                    'total' => 0,
                    'completed' => 0,
                    'partial' => 0,
                    'errored' => 0,
                    'skipped' => 0,
                ],
            ])->toArray(),
            'recent_errors' => [],
        ];

        foreach ($translations as $translation) {
            if (isset($progress['by_language'][$translation->language_id])) {
                $progress['by_language'][$translation->language_id]['total']++;
            }
        }

        $this->updateProgress($progress, null, null, true);

        try {
            $tts = new AzureTTSService();

            foreach ($translations as $translation) {
                if ($this->shouldStop()) {
                    $this->updateProgress($progress, 'stopped', 'Audio conversion stopped by user', true);

                    return;
                }

                $this->convertTranslationRow($translation, $tts, $progress);
            }

            if ($progress['status'] === 'running') {
                $this->updateProgress($progress, 'completed', $this->summaryMessage($progress), true);
            }
        } catch (\Throwable $e) {
            Log::error('Audio conversion job failed: '.$e->getMessage());
            $this->updateProgress($progress, 'error', 'Audio conversion failed: '.$e->getMessage(), true);
        }
    }

    private function convertTranslationRow(QuestionTranslation $translation, AzureTTSService $tts, array &$progress): void
    {
        $language = $translation->language;
        if (! $language) {
            $this->bumpOutcome($progress, null, 'skipped');

            return;
        }

        $fieldsWithText = [];
        $missingAudio = [];

        foreach (self::FIELDS as $field) {
            $text = $translation->{"{$field}_translation"};
            if (empty($text)) {
                continue;
            }

            $fieldsWithText[] = $field;

            if (empty($translation->{"{$field}_audio"})) {
                $missingAudio[] = $field;
            }
        }

        if (empty($fieldsWithText)) {
            $this->bumpOutcome($progress, $language, 'skipped');

            return;
        }

        if (empty($missingAudio)) {
            $this->bumpOutcome($progress, $language, 'completed');

            return;
        }

        $failedFields = [];

        foreach ($missingAudio as $field) {
            if ($this->shouldStop()) {
                $this->updateProgress($progress, 'stopped', 'Audio conversion stopped by user', true);

                return;
            }

            $text = $translation->{"{$field}_translation"};
            $audioContent = $tts->convertToSpeech($text, $language);

            if (is_array($audioContent) && ($audioContent['status'] ?? false) === false) {
                $failedFields[] = $field;
                $this->recordFailure(
                    $translation,
                    $language,
                    [$field],
                    $audioContent['message'] ?? 'TTS conversion failed',
                    $progress
                );

                return;
            }

            if (! is_array($audioContent) && ! empty($audioContent)) {
                $path = public_path('audios');
                $fileName = "{$field}_".time().'_'.$translation->id.'.mp3';
                file_put_contents($path.'/'.$fileName, $audioContent);
                $translation->update(["{$field}_audio" => $fileName]);
                usleep(500000);
            }
        }

        $translation->refresh();

        $stillMissing = array_values(array_filter(
            $fieldsWithText,
            fn ($field) => empty($translation->{"{$field}_audio"})
        ));

        if (! empty($failedFields)) {
            return;
        }

        $outcome = empty($stillMissing) ? 'completed' : 'partial';
        $this->bumpOutcome($progress, $language, $outcome, $translation, $stillMissing);
    }

    private function recordFailure(
        QuestionTranslation $translation,
        Language $language,
        array $fields,
        string $reason,
        array &$progress
    ): void {
        $this->bumpOutcome($progress, $language, 'errored', $translation, $fields, $reason);
        Log::warning("TTS conversion failed: {$reason}");
    }

    private function bumpOutcome(
        array &$progress,
        ?Language $language,
        string $outcome,
        ?QuestionTranslation $translation = null,
        array $fields = [],
        ?string $reason = null
    ): void {
        $progress['processed']++;
        $progress[$outcome]++;

        if ($language && isset($progress['by_language'][$language->id])) {
            $progress['by_language'][$language->id][$outcome]++;
        }

        if ($outcome === 'errored' && $translation && $language) {
            $progress['recent_errors'][] = [
                'question_id' => $translation->question_id,
                'language_id' => $language->id,
                'language' => $language->name,
                'fields' => $fields,
                'reason' => $reason,
                'at' => now()->toDateTimeString(),
            ];

            if (count($progress['recent_errors']) > self::MAX_RECENT_ERRORS) {
                array_shift($progress['recent_errors']);
            }
        }

        if ($outcome === 'partial' && $translation && $language && ! empty($fields)) {
            $progress['message'] = sprintf(
                'Partial audio for question #%d (%s): still missing %s',
                $translation->question_id,
                $language->name,
                implode(', ', $fields)
            );
        }

        $this->updateProgress($progress, null, null, $outcome === 'errored');
    }

    private function summaryMessage(array $progress): string
    {
        return sprintf(
            'Audio conversion completed: %d done, %d partial, %d errored, %d skipped.',
            $progress['completed'],
            $progress['partial'],
            $progress['errored'],
            $progress['skipped']
        );
    }

    private function shouldStop(): bool
    {
        return (bool) Cache::get('tts_stop_flag');
    }

    private function updateProgress(array &$progress, ?string $status = null, ?string $message = null, bool $force = false): void
    {
        if ($status) {
            $progress['status'] = $status;
        }
        if ($message) {
            $progress['message'] = $message;
        }

        $this->pendingProgressWrites++;

        if ($force || $status !== null || $this->pendingProgressWrites >= self::PROGRESS_FLUSH_EVERY) {
            Cache::put('tts_progress', $progress, 3600);
            $this->pendingProgressWrites = 0;
        }
    }
}
