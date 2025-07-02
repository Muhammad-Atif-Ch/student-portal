<?php

namespace App\Jobs;

use App\Models\Language;
use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Services\AzureTextToSpeech\AzureTTSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SingleQuestionTranslationJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected string $apiKey;
  protected string $sourceLanguage;
  protected Question $question;

  public function __construct(Question $question)
  {
    $this->apiKey = env('GOOGLE_TRANSLATE_API_KEY');
    $this->sourceLanguage = 'en';
    $this->question = $question;
  }

  public function handle()
  {
    try {
      $languages = Language::where('status', 'active')->get();

      $progress = [
        'total' => $languages->count() * 7, // 6 fields + 1 for initial setup
        'completed' => 0,
        'status' => 'running',
        'message' => 'Starting translation and voice conversion...',
        'question_id' => $this->question->id
      ];
      $this->updateProgress($progress);

      foreach ($languages as $language) {
        if ($this->shouldStop()) {
          $this->updateProgress($progress, 'stopped', 'Process stopped by user');
          return;
        }

        // Translate all fields
        $translations = $this->translateFields($this->question, $language, $progress);
        if ($translations === false) {
          continue;
        }

        // Save translation
        $translation = $this->saveTranslation($this->question, $language, $translations);

        // Convert to speech
        $this->convertToSpeech($translation, $language, $progress);
      }

      $this->updateProgress($progress, 'completed', 'Translation and voice conversion completed successfully');
    } catch (\Exception $e) {
      Log::error('Single question translation job failed: ' . $e->getMessage());
      $this->updateProgress($progress, 'error', 'Process failed: ' . $e->getMessage());
    }
  }

  private function shouldStop(): bool
  {
    return Cache::get('translation_immediate_stop') ||
      Cache::get('translation_stop_flag') ||
      Cache::get('translation_force_stop');
  }

  private function translateFields(Question $question, Language $language, array &$progress): array|false
  {
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
      if ($this->shouldStop()) {
        return false;
      }

      if (empty($text)) {
        $progress['completed']++;
        $progress['message'] = "Skipping empty field {$key} for language {$language->name}";
        $this->updateProgress($progress);
        continue;
      }

      try {
        $translated = $this->translate($text, $language->code);
        if ($translated === false) {
          Log::error("Translation failed for field: {$key}");
          return false;
        }
        $translations[$key] = $translated;

        // $progress['completed']++;
        // $progress['message'] = "Translated {$key} for language {$language->name}";
        // $this->updateProgress($progress);
      } catch (\Exception $e) {
        Log::error("Error translating field {$key}: " . $e->getMessage());
        return false;
      }

      // Prevent rate limiting
      usleep(500000);
    }

    return $translations;
  }

  private function translate(?string $text, string $targetLanguage): string|false
  {
    if (empty($text)) {
      return '';
    }

    try {
      $response = Http::get('https://translation.googleapis.com/language/translate/v2', [
        'key' => $this->apiKey,
        'q' => $text,
        'target' => $targetLanguage,
        'format' => 'text'
      ]);

      if ($response->successful()) {
        return $response->json()['data']['translations'][0]['translatedText'] ?? $text;
      }

      return $text;
    } catch (\Exception $e) {
      Log::error("Translation API error: " . $e->getMessage());
      return $text;
    }
  }

  private function saveTranslation(Question $question, Language $language, array $translations): QuestionTranslation
  {
    $translationData = [
      'quiz_id' => $question->quiz_id,
      'question_translation' => $translations['question'] ?? null,
      'a_translation' => $translations['a'] ?? null,
      'b_translation' => $translations['b'] ?? null,
      'c_translation' => $translations['c'] ?? null,
      'd_translation' => $translations['d'] ?? null,
      'answer_explanation_translation' => $translations['answer_explanation'] ?? null,
    ];

    return QuestionTranslation::updateOrCreate(
      [
        'question_id' => $question->id,
        'language_id' => $language->id
      ],
      $translationData
    );
  }

  private function convertToSpeech(QuestionTranslation $translation, Language $language, array &$progress)
  {
    try {
      $tts = new AzureTTSService();

      $fields = [
        'question' => $translation->question_translation ?? null,
        'a' => $translation->a_translation ?? null,
        'b' => $translation->b_translation ?? null,
        'c' => $translation->c_translation ?? null,
        'd' => $translation->d_translation ?? null,
        'answer_explanation' => $translation->answer_explanation_translation ?? null
      ];

      foreach ($fields as $field => $text) {
        if ($this->shouldStop()) {
          return;
        }

        if (empty($text)) {
          $progress['completed']++;
          $progress['message'] = "Skipping voice conversion for empty field {$field} in {$language->name}";
          $this->updateProgress($progress);
          continue;
        }

        $audioField = "{$field}_audio";

        // Convert text to speech
        $audioContent = $tts->convertToSpeech($text, $language->code);
        if ($audioContent === false) {
          Log::error("TTS conversion failed for translation ID: {$translation->id}, field: {$field}");
          continue;
        }

        // Save audio file
        $path = public_path('audios');
        $fileName = "{$field}_" . time() . '.mp3';
        file_put_contents($path . '/' . $fileName, $audioContent);

        // Update translation record
        $translation->update([$audioField => $fileName]);

        $progress['completed']++;
        $progress['message'] = "Converted {$field} to speech for language {$language->name}";
        $this->updateProgress($progress);

        // Prevent rate limiting
        usleep(500000);
      }
    } catch (\Exception $e) {
      Log::error('Audio conversion failed: ' . $e->getMessage());
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

    // Store progress in cache with unique key for this question
    Cache::put("translation_progress_question_{$this->question->id}", $progress, 3600);
  }
}