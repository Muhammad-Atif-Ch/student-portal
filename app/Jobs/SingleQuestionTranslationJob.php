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
  protected $updateType;

  public function __construct(Question $question, string $updateType)
  {
    $this->apiKey = env('GOOGLE_TRANSLATE_API_KEY');
    $this->sourceLanguage = 'en';
    $this->question = $question;
    $this->updateType = $updateType;
  }

  public function handle()
  {
    $progress = [
      'total' => 0,
      'completed' => 0,
      'status' => 'running',
      'message' => 'Starting translation and voice conversion...',
      'question_id' => $this->question->id
    ];

    try {
      // Log::info('Job started', ['job' => static::class, 'update_type' => $this->updateType]);
      $languages = Language::where('status', '1')->get();

      $progress['total'] = $languages->count() * 7;
      $this->updateProgress($progress);

      foreach ($languages as $language) {
        // Log::info('Translating foreach start', ['stop or not' => $this->shouldStop()]);
        if ($this->shouldStop()) {
          $this->updateProgress($progress, 'stopped', 'Process stopped by user');
          return;
        }

        $result = $this->processLanguage($language, $progress);

        if ($result === false) {
          Log::warning("Failed to process language: {$language->name} for question: {$this->question->id}");
          continue;
        }
      }

      $this->updateProgress($progress, 'completed', 'Translation completed successfully');
    } catch (\Exception $e) {
      Log::error('Single question translation job failed: ' . $e->getMessage());
      $this->updateProgress($progress, 'error', 'Process failed: ' . $e->getMessage());
    }
  }

  private function processLanguage(Language $language, array &$progress): bool
  {
    try {
      switch ($this->updateType) {
        case 'all_data_update':
          return $this->translationAudioUpdate($language, $progress);
        case 'translation':
          return $this->translation($language, $progress);
        case 'audio':
          return $this->audio($language, $progress);
        default:
          Log::warning("Unknown update type: {$this->updateType}");
          return false;
      }
    } catch (\Exception $e) {
      Log::error("Failed to process language {$language->name}", [
        'error' => $e->getMessage(),
        'question_id' => $this->question->id,
        'language_id' => $language->id
      ]);
      return false;
    }
  }

  //  private function copycodeonly()
  // {
  //   try {
  //     // Log::info('Job started', ['job' => static::class, 'update_type' => $this->updateType]);
  //     $languages = Language::where('status', '1')->get();

  //     $progress = [
  //       'total' => $languages->count() * 7, // 6 fields + 1 for initial setup
  //       'completed' => 0,
  //       'status' => 'running',
  //       'message' => 'Starting translation and voice conversion...',
  //       'question_id' => $this->question->id
  //     ];
  //     $this->updateProgress($progress);

  //     foreach ($languages as $language) {
  //       // Log::info('Translating foreach start', ['stop or not' => $this->shouldStop()]);
  //       if ($this->shouldStop()) {
  //         $this->updateProgress($progress, 'stopped', 'Process stopped by user');
  //         return;
  //       }

  //       // Translate all fields
  //       $translations = $this->translateFields($this->question, $language, $progress);
  //       if ($translations === false) {
  //         continue;
  //       }

  //       // Save translation
  //       $translation = $this->saveTranslation($this->question, $language, $translations);

  //       // Convert to speech
  //       $this->convertToSpeech($translation, $language, $progress);
  //     }

  //     $this->updateProgress($progress, 'completed', 'Translation completed successfully');
  //   } catch (\Exception $e) {
  //     Log::error('Single question translation job failed: ' . $e->getMessage());
  //     $this->updateProgress($progress, 'error', 'Process failed: ' . $e->getMessage());
  //   }
  // }

  private function translationAudioUpdate($language, array &$progress)
  {
    // Translate all fields
    $translations = $this->translateFields($this->question, $language, $progress);
    if ($translations === false) {
      return false;
    }

    // Save translation
    $translation = $this->saveTranslation($this->question, $language, $translations);

    // Convert to speech
    $this->convertToSpeech($translation, $language, $progress);
    return true;
  }

  private function translation($language, array &$progress)
  {
    // Translate all fields
    $translations = $this->translateFields($this->question, $language, $progress);
    if ($translations === false) {
      return false;
    }

    // Save translation
    $this->saveTranslation($this->question, $language, $translations);
    return true;
  }

  private function audio(Language $language, array &$progress)
  {
    try {
      $translation = QuestionTranslation::where('question_id', $this->question->id)
        ->where('language_id', $language->id)
        ->first();

      if (!$translation) {
        Log::warning("No translation found for audio conversion", [
          'question_id' => $this->question->id,
          'language_id' => $language->id,
          'language_name' => $language->name
        ]);
        return false;
      }

      Log::info('Starting audio conversion', [
        'translation_id' => $translation->id,
        'language' => $language->name
      ]);

      // Convert to speech
      $this->convertToSpeech($translation, $language, $progress);
      return true;
    } catch (\Exception $e) {
      Log::error("Audio conversion failed for {$language->name}", [
        'error' => $e->getMessage(),
        'question_id' => $this->question->id
      ]);
      return false;
    }
  }

  private function shouldStop(): bool
  {
    // Log::info("Job stop check", [
    //   'immediate_stop' => Cache::get('translation_immediate_stop'),
    //   'stop_flag' => Cache::get('translation_stop_flag'),
    //   'force_stop' => Cache::get('translation_force_stop')
    // ]);
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

        Log::info("Translated field {$key}", [
          'original' => $text,
          'translated' => $translated,
          'language' => $language->name
        ]);
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
    try {
      $translationData = [
        'quiz_id' => $question->quiz_id,
        'question_translation' => $translations['question'] ?? null,
        'a_translation' => $translations['a'] ?? null,
        'b_translation' => $translations['b'] ?? null,
        'c_translation' => $translations['c'] ?? null,
        'd_translation' => $translations['d'] ?? null,
        'answer_explanation_translation' => $translations['answer_explanation'] ?? null,
      ];

      // Log translation data
      // Log::info('Saving translation', [
      //   'question_id' => $question->id,
      //   'language_id' => $language->id,
      //   'translations' => $translations
      // ]);

      return QuestionTranslation::updateOrCreate(
        [
          'question_id' => $question->id,
          'language_id' => $language->id
        ],
        $translationData
      );
    } catch (\Exception $e) {
      Log::error('Logging translation data failed: ' . $e->getMessage());
      return new QuestionTranslation(); // Return empty model on failure
    }
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
        $audioContent = $tts->convertToSpeech($text, $language);
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

    // Log::info('Progress updated', ['progress' => $progress]);
  }
}