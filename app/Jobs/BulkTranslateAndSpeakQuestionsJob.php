<?php

namespace App\Jobs;

use App\Models\Language;
use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Services\AzureTextToSpeech\AzureTTSService;
use App\Services\AzureTranslation\AzureTranslatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BulkTranslateAndSpeakQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const FIELDS = ['question', 'a', 'b', 'c', 'd', 'answer_explanation'];

    private const MAX_RECENT_ERRORS = 200;

    private const PROGRESS_FLUSH_EVERY = 1;

    public int $timeout = 0;

    public int $tries = 1;

    private int $pendingWrites = 0;

    private const MAX_LOG_FEED = 100;

    public function handle(AzureTranslatorService $translator, AzureTTSService $tts)
    {
        $languages = Language::where('status', 1)->get();
        $totalQuestions = Question::whereNotNull('question')->where('question', '!=', '')->count();

        $progress = [
            'total' => $totalQuestions * $languages->count(),
            'processed' => 0,
            'completed' => 0,
            'partial' => 0,
            'errored' => 0,
            'skipped' => 0,
            'status' => 'running',
            'message' => 'Starting translation + voice generation...',
            'by_language' => $languages->mapWithKeys(fn ($l) => [
                $l->id => ['name' => $l->name, 'total' => $totalQuestions, 'completed' => 0, 'partial' => 0, 'errored' => 0, 'skipped' => 0],
            ])->toArray(),
            'recent_errors' => [],
            'log_feed' => [],
        ];
        $this->flush($progress, force: true);

        try {
            Question::whereNotNull('question')->where('question', '!=', '')
                ->orderBy('quiz_id')->orderBy('id')
                ->chunk(50, function ($questions) use ($languages, $translator, $tts, &$progress) {
                    foreach ($questions as $question) {
                        foreach ($languages as $language) {
                            if ($this->shouldStop()) {
                                $this->flush($progress, 'stopped', 'Stopped by user', true);

                                return false;
                            }
                            $this->processPair($question, $language, $translator, $tts, $progress);
                        }
                    }
                    $this->flush($progress, force: true);
                });

            if ($progress['status'] === 'running') {
                $this->flush($progress, 'completed', sprintf(
                    'Done: %d completed, %d partial, %d errored, %d skipped.',
                    $progress['completed'], $progress['partial'], $progress['errored'], $progress['skipped']
                ), true);
            }
        } catch (\Throwable $e) {
            Log::error('Combined translate+speak job failed: '.$e->getMessage());
            $this->flush($progress, 'error', 'Failed: '.$e->getMessage(), true);
        }
    }

    private function processPair(Question $question, Language $language, AzureTranslatorService $translator, AzureTTSService $tts, array &$progress): void
    {
        $sourceFields = $translator->resolveSourceFields($question, $language);
        $expected = array_filter(self::FIELDS, fn ($k) => ! empty($sourceFields[$k] ?? null));

        if (empty($expected)) {
            $this->bump($progress, $language, 'skipped');

            return;
        }

        $translation = QuestionTranslation::firstOrNew(
            ['question_id' => $question->id, 'language_id' => $language->id],
            ['quiz_id' => $question->quiz_id]
        );

        // --- Step 1: translate missing text ---
        $missingText = [];
        foreach ($expected as $key) {
            if (empty($translation->{"{$key}_translation"})) {
                $missingText[$key] = $sourceFields[$key];
            }
        }

        if (! empty($missingText)) {
            try {
                $translated = $translator->translateBatch($missingText, $language->code);
            } catch (\Throwable $e) {
                $this->recordFailure($question, $language, $translation, array_keys($missingText), $e->getMessage(), $progress);

                return;
            }

            if ($translated === false) {
                $this->recordFailure(
                    $question,
                    $language,
                    $translation,
                    array_keys($missingText),
                    'Azure Translator returned no result (check storage/logs/laravel.log).',
                    $progress
                );

                return;
            }

            foreach ($translated as $key => $text) {
                $translation->{"{$key}_translation"} = $text;
            }
            $translation->quiz_id = $question->quiz_id;
            $translation->save();
        }

        // --- Step 2: generate audio for anything now translated but missing audio ---
        $missingAudio = array_filter($expected, fn ($k) => empty($translation->{"{$k}_translation"}) === false && empty($translation->{"{$k}_audio"}));
        $audioFailed = [];

        foreach ($missingAudio as $field) {
            if ($this->shouldStop()) {
                $this->flush($progress, 'stopped', 'Stopped by user', true);

                return;
            }

            $audio = $tts->convertToSpeech($translation->{"{$field}_translation"}, $language);

            if (is_array($audio) && ($audio['status'] ?? false) === false) {
                $audioFailed[] = $field;

                continue;
            }

            if (! is_array($audio) && ! empty($audio)) {
                $fileName = "{$field}_".time().'_'.($translation->id ?? uniqid()).'.mp3';
                file_put_contents(public_path('audios/'.$fileName), $audio);
                $translation->{"{$field}_audio"} = $fileName;
                usleep(300000);
            }
        }

        $status = $translator->resolveStatus($question, $language, $translation, requireAudio: true);
        $translation->status = $audioFailed ? 'partial' : $status;
        $translation->error = $audioFailed ? 'Audio failed for: '.implode(', ', $audioFailed) : null;
        $translation->save();

        $this->bump($progress, $language, $translation->status === 'completed' ? 'completed' : 'partial', $question, $audioFailed);
        usleep(150000);
    }

    private function recordFailure(Question $q, Language $l, QuestionTranslation $t, array $fields, string $reason, array &$progress): void
    {
        $t->quiz_id = $q->quiz_id;
        $t->status = 'error';
        $t->error = $reason;
        $t->save();
        $this->bump($progress, $l, 'errored', $q, $fields, $reason);
        Log::warning("Combined job failed for question {$q->id}/{$l->id}: {$reason}");
    }

    private function bump(array &$progress, Language $language, string $outcome, ?Question $question = null, array $fields = [], ?string $reason = null): void
    {
        $progress['processed']++;
        $progress[$outcome]++;
        if (isset($progress['by_language'][$language->id])) {
            $progress['by_language'][$language->id][$outcome]++;
        }
        if ($question) {
            $progress['log_feed'][] = [
                'question_id' => $question->id,
                'language' => $language->name,
                'outcome' => $outcome, // completed | partial | errored | skipped
                'fields' => $fields,
                'reason' => $reason,
                'at' => now()->format('H:i:s'),
            ];
            if (count($progress['log_feed']) > self::MAX_LOG_FEED) {
                array_shift($progress['log_feed']);
            }
        }
        if ($outcome === 'errored' && $question) {
            $progress['recent_errors'][] = [
                'question_id' => $question->id, 'language_id' => $language->id, 'language' => $language->name,
                'fields' => $fields, 'reason' => $reason, 'at' => now()->toDateTimeString(),
            ];
            if (count($progress['recent_errors']) > self::MAX_RECENT_ERRORS) {
                array_shift($progress['recent_errors']);
            }
        }
        $this->flush($progress, force: $outcome === 'errored');
    }

    private function shouldStop(): bool
    {
        return (bool) Cache::get('combined_stop_flag');
    }

    private function flush(array &$progress, ?string $status = null, ?string $message = null, bool $force = false): void
    {
        if ($status) {
            $progress['status'] = $status;
        }
        if ($message) {
            $progress['message'] = $message;
        }
        $this->pendingWrites++;
        if ($force || $status !== null || $this->pendingWrites >= self::PROGRESS_FLUSH_EVERY) {
            Cache::put('combined_progress', $progress, 3600);
            $this->pendingWrites = 0;
        }
    }
}
