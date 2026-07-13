<?php

namespace App\Jobs;

use App\Models\Language;
use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Services\AzureTranslation\AzureTranslatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BulkTranslateQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Field keys we translate, in a fixed order. */
    private const FIELDS = ['question', 'a', 'b', 'c', 'd', 'answer_explanation'];

    /** How many recent per-item errors to keep in the progress cache for the frontend. */
    private const MAX_RECENT_ERRORS = 200;

    /** Flush the progress cache at most this often (in processed pairs), except on errors/terminal states. */
    private const PROGRESS_FLUSH_EVERY = 20;

    // No queue timeout - this can run long. Rely on the stop flag instead.
    public int $timeout = 0;

    // Retrying the whole job on failure is safe now (missing fields are
    // recomputed fresh each run), but default to one attempt so a
    // transient error surfaces instead of silently re-running everything.
    public int $tries = 1;

    private int $pendingProgressWrites = 0;

    public function handle(AzureTranslatorService $translator)
    {
        // $key = config('services.azure_translator.key');
        // if (empty(config('services.azure_translator.key'))) {
        //     $this->fail('Azure Translate API key is not configured.'.$key);

        //     return;
        // }
        $key = config('services.azure_translator.key');
        
        Log::info('Azure Translator config check', [
            'has_key' => ! empty($key),
            'key_preview' => $key ? (substr($key, 0, 4).'...'.substr($key, -4)) : null,
            'region' => config('services.azure_translator.region'),
            'endpoint' => config('services.azure_translator.endpoint'),
        ]);

        $languages = Language::where('status', 1)->get();
        $totalQuestions = Question::whereNotNull('question')->where('question', '!=', '')->count();

        $progress = [
            'total' => $totalQuestions * $languages->count(),
            // 'processed' drives the percentage bar - it counts every pair
            // the loop has looked at, regardless of outcome, so it always
            // reaches 100% when the run finishes.
            'processed' => 0,
            'completed' => 0,
            'partial' => 0,
            'errored' => 0,
            'skipped' => 0,
            'status' => 'running',
            'message' => 'Starting translation...',
            'by_language' => $languages->mapWithKeys(fn ($language) => [
                $language->id => [
                    'name' => $language->name,
                    'total' => $totalQuestions,
                    'completed' => 0,
                    'partial' => 0,
                    'errored' => 0,
                    'skipped' => 0,
                ],
            ])->toArray(),
            // Bounded so a 10,000-pair run doesn't grow the cache payload
            // without limit. getReport() on the controller is the
            // authoritative, DB-backed source of truth beyond this window.
            'recent_errors' => [],
        ];
        $this->updateProgress($progress, null, null, true);

        try {
            Question::whereNotNull('question')
                ->where('question', '!=', '')
                ->orderBy('quiz_id')
                ->orderBy('id')
                ->chunk(100, function ($questions) use ($languages, $translator, &$progress) {
                    foreach ($questions as $question) {
                        foreach ($languages as $language) {
                            if ($this->shouldStop()) {
                                $this->updateProgress($progress, 'stopped', 'Translation stopped by user', true);

                                return false; // stop chunk() from pulling more pages
                            }

                            $this->translateQuestionForLanguage($question, $language, $translator, $progress);
                        }
                    }

                    // Guarantee the frontend never waits more than one
                    // chunk (100 questions) behind, even if the per-pair
                    // throttle below didn't flush.
                    $this->updateProgress($progress, null, null, true);
                });

            if ($progress['status'] === 'running') {
                $this->updateProgress($progress, 'completed', $this->summaryMessage($progress), true);
            }
        } catch (\Throwable $e) {
            Log::error('Translation job failed: '.$e->getMessage());
            $this->updateProgress($progress, 'error', 'Translation failed: '.$e->getMessage(), true);
        }
    }

    /**
     * Handles a single question/language pair. Per field: if the question
     * has content for it, make sure it's translated; if it doesn't (e.g.
     * no option d), that field is simply never sent and never counted -
     * it's not "missing", there was just nothing to translate.
     */
    private function translateQuestionForLanguage(Question $question, Language $language, AzureTranslatorService $translator, array &$progress): void
    {
        $sourceFields = $translator->resolveSourceFields($question, $language);

        $existing = QuestionTranslation::where([
            'question_id' => $question->id,
            'language_id' => $language->id,
        ])->first();

        $missing = [];   // fields with source content that still need translating
        $expected = [];  // fields with source content at all (used to judge complete vs partial)

        foreach (self::FIELDS as $key) {
            $source = $sourceFields[$key] ?? null;

            if (empty($source)) {
                continue; // nothing on the question for this field - skip it, not an error
            }

            $expected[] = $key;

            if (empty($existing?->{"{$key}_translation"})) {
                $missing[$key] = $source;
            }
        }

        // The question has no translatable content at all.
        if (empty($expected)) {
            $this->bumpOutcome($progress, $language, 'skipped');

            return;
        }

        // Every field that has content is already translated.
        if (empty($missing)) {
            $this->bumpOutcome($progress, $language, 'completed');

            return;
        }

        try {
            $translated = $translator->translateBatch($missing, $language->code);
        } catch (\Throwable $e) {
            // This is the real fix for "I don't know what error": once
            // AzureTranslatorService throws instead of silently returning
            // false, $e->getMessage() carries Azure's actual error text
            // (auth failure, quota, bad request, etc.) instead of a
            // generic "request failed for: field, field, field" line.
            $this->recordFailure($question, $language, array_keys($missing), $e->getMessage(), $progress);

            return;
        }

        if ($translated === false) {
            // Defensive fallback in case the service still returns false
            // in some path instead of throwing - at least say so plainly
            // rather than guessing at a reason.
            $this->recordFailure(
                $question,
                $language,
                array_keys($missing),
                'Azure Translator returned no result. Check storage/logs/laravel.log for the raw API response.',
                $progress
            );

            return;
        }

        $status = $this->saveTranslation($question, $language, $translated, $existing, $expected);
        $this->bumpOutcome($progress, $language, $status);

        // One request now covers every missing field for this pair, so a
        // short pause is enough to stay well under Azure's rate limits.
        usleep(150000);
    }

    private function recordFailure(Question $question, Language $language, array $fields, string $reason, array &$progress): void
    {
        QuestionTranslation::updateOrCreate(
            ['question_id' => $question->id, 'language_id' => $language->id],
            ['quiz_id' => $question->quiz_id, 'status' => 'error', 'error' => $reason]
        );

        $this->bumpOutcome($progress, $language, 'errored', $question, $fields, $reason);
        // Log::warning("Translation failed for question {$question->id} / language {$language->id}: {$reason}");
        Log::warning("Translation failed {$reason}");
    }

    /**
     * @return string 'completed' if every field with source content ended up translated, 'partial' otherwise
     */
    private function saveTranslation(Question $question, Language $language, array $translated, ?QuestionTranslation $existing, array $expectedFields): string
    {
        $data = ['quiz_id' => $question->quiz_id];

        foreach (self::FIELDS as $key) {
            $data["{$key}_translation"] = $translated[$key] ?? $existing?->{"{$key}_translation"};
        }

        $stillMissing = array_values(array_filter(
            $expectedFields,
            fn ($key) => empty($data["{$key}_translation"])
        ));

        $data['status'] = empty($stillMissing) ? 'completed' : 'partial';
        $data['error'] = empty($stillMissing) ? null : 'Still missing after translation: '.implode(', ', $stillMissing);

        QuestionTranslation::updateOrCreate(
            ['question_id' => $question->id, 'language_id' => $language->id],
            $data
        );

        return $data['status'];
    }

    /**
     * Records one pair's outcome into the aggregate counters, the
     * per-language breakdown, and (for errors) the bounded recent-errors
     * feed - this is what lets the frontend show a real report instead of
     * a single percentage.
     */
    private function bumpOutcome(array &$progress, Language $language, string $outcome, ?Question $question = null, array $fields = [], ?string $reason = null): void
    {
        $progress['processed']++;
        $progress[$outcome]++;

        if (isset($progress['by_language'][$language->id])) {
            $progress['by_language'][$language->id][$outcome]++;
        }

        if ($outcome === 'errored' && $question) {
            $progress['recent_errors'][] = [
                'question_id' => $question->id,
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

        // Always flush immediately on an error so the frontend surfaces it
        // right away; otherwise throttle writes so a 10,000-pair run doesn't
        // hammer the cache store on every single iteration.
        $this->updateProgress($progress, null, null, $outcome === 'errored');
    }

    private function summaryMessage(array $progress): string
    {
        return sprintf(
            'Translation completed: %d done, %d partial, %d errored, %d skipped.',
            $progress['completed'],
            $progress['partial'],
            $progress['errored'],
            $progress['skipped']
        );
    }

    /**
     * Cheap, cache-only check. The controller's stopTranslation() writes
     * this flag; polling the DB here (as the old code did) was an
     * unnecessary hit on every single iteration.
     */
    private function shouldStop(): bool
    {
        return (bool) Cache::get('translation_stop_flag');
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
            Cache::put('translation_progress', $progress, 3600);
            $this->pendingProgressWrites = 0;
        }
    }
}
