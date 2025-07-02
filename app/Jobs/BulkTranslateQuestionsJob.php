<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\Language;
use App\Models\Question;
use App\Models\QuestionTranslation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BulkTranslateQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $apiKey,
        protected string $sourceLanguage,
        protected int $batchSize = 5
    ) {
    }

    public function handle()
    {
        try {
            $questions = Question::whereNotNull('question')->where('question', '!=', '')
                // ->take(20)
                ->orderBy('quiz_id', 'asc')->get();
            $languages = Language::where('status', 'active')->get();

            $progress = [
                'total' => $questions->count() * $languages->count(),
                'completed' => 0,
                'status' => 'running',
                'message' => 'Starting translation...'
            ];
            $this->updateProgress($progress);

            foreach ($questions as $question) {
                foreach ($languages as $language) {
                    if ($this->shouldStop()) {
                        $this->updateProgress($progress, 'stopped', 'Translation stopped by user');
                        return;
                    }

                    // Skip if already translated
                    if ($this->isAlreadyTranslated($question, $language)) {
                        $progress['completed']++;
                        $this->updateProgress($progress);
                        continue;
                    }

                    // Translate all fields
                    $translations = $this->translateFields($question, $language);
                    // Log::error('translations check: ', $translations);
                    if ($translations === false) {
                        return;
                    }

                    // Save translation
                    $this->saveTranslation($question, $language, $translations);

                    $progress['completed']++;
                    $this->updateProgress($progress);

                    // Prevent rate limiting
                    usleep(500000);
                }
            }

            $this->updateProgress($progress, 'completed', 'Translation completed successfully');
        } catch (\Exception $e) {
            Log::error('Translation job failed: ' . $e->getMessage());
            $this->updateProgress($progress, 'error', 'Translation failed: ' . $e->getMessage());
        }
    }

    private function shouldStop(): bool
    {
        return Cache::get('translation_immediate_stop') ||
            Setting::first()?->translation_stopped ||
            Cache::get('translation_stop_flag') ||
            Cache::get('translation_force_stop');
    }

    private function isAlreadyTranslated(Question $question, Language $language): bool
    {
        return QuestionTranslation::where([
            'question_id' => $question->id,
            'language_id' => $language->id
        ])->exists();
    }

    private function translateFields(Question $question, Language $language): array|false
    {
        // Initialize translations array with all keys set to null
        $translations = [
            'question' => null,
            'a' => null,
            'b' => null,
            'c' => null,
            'd' => null,
            'answer_explanation' => null
        ];

        if ($language->id == 39) {
            // 👉 Force use original fields only
            $fields = [
                'question' => $question->question,
                'a' => $question->a,
                'b' => $question->b,
                'c' => $question->c,
                'd' => $question->d,
                'answer_explanation' => $question->answer_explanation,
            ];
        } else {
            // 👉 Use translation if available, fallback to original
            $fields = [
                'question' => $question->question_translation ?: $question->question ?: null,
                'a' => $question->a_translation ?: $question->a ?: null,
                'b' => $question->b_translation ?: $question->b ?: null,
                'c' => $question->c_translation ?: $question->c ?: null,
                'd' => $question->d_translation ?: $question->d ?: null,
                'answer_explanation' => $question->answer_explanation_translation ?: $question->answer_explanation ?: null,
            ];
        }

        foreach ($fields as $key => $text) {
            if (empty($text)) {
                Log::info("Skipping empty field: {$key}");
                continue;
            }

            try {
                $translated = $this->translate($text, $language->code);
                if ($translated === false) {
                    Log::error("Translation failed for field: {$key}");
                    return false;
                }
                $translations[$key] = $translated;
            } catch (\Exception $e) {
                Log::error("Error translating field {$key}: " . $e->getMessage(), [
                    'question_id' => $question->id,
                    'language_id' => $language->id,
                    'text' => $text
                ]);
                return false;
            }
        }

        return $translations;
    }

    private function translate(?string $text, string $targetLanguage): string|false
    {
        if (empty($text) || $this->shouldStop()) {
            return $this->shouldStop() ? false : '';
        }

        try {
            $response = Http::get('https://translation.googleapis.com/language/translate/v2', [
                'key' => $this->apiKey,
                'q' => $text,
                'target' => $targetLanguage,
                'format' => 'text'
            ]);

            if ($this->shouldStop()) {
                return false;
            }

            if ($response->successful()) {
                return $response->json()['data']['translations'][0]['translatedText'] ?? $text;
            }

            return $text;
        } catch (\Exception $e) {
            Log::error("Translation API error: " . $e->getMessage());
            return $text;
        }
    }

    private function saveTranslation(Question $question, Language $language, array $translations): void
    {
        try {
            // Ensure all required keys exist with null defaults
            $translationData = [
                'quiz_id' => $question->quiz_id,
                'question_translation' => $translations['question'] ?? null,
                'a_translation' => $translations['a'] ?? null,
                'b_translation' => $translations['b'] ?? null,
                'c_translation' => $translations['c'] ?? null,
                'd_translation' => $translations['d'] ?? null,
                'answer_explanation_translation' => $translations['answer_explanation'] ?? null,
            ];

            Log::info('Saving translation:', [
                'question_id' => $question->id,
                'language_id' => $language->id,
                'data' => $translationData
            ]);

            QuestionTranslation::updateOrCreate(
                [
                    'question_id' => $question->id,
                    'language_id' => $language->id
                ],
                $translationData
            );
        } catch (\Exception $e) {
            Log::error('Failed to save translation: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'language_id' => $language->id,
                'translations' => $translations
            ]);
            throw $e;
        }
    }

    private function updateProgress(array &$progress, ?string $status = null, ?string $message = null): void
    {
        if ($status) {
            $progress['status'] = $status;
        }
        if ($message) {
            $progress['message'] = $message;
        }
        Cache::put('translation_progress', $progress, 3600);
    }
}
