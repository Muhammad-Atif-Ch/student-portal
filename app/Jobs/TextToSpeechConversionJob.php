<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\QuestionTranslation;
use App\Helpers\UploadFile;
use App\Models\Language;
use App\Services\AzureTextToSpeech\AzureTTSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TextToSpeechConversionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $batchSize;

    public function __construct(int $batchSize = 5)
    {
        $this->batchSize = $batchSize;
    }

    public function handle()
    {
        try {
            $languages = Language::where('status', 1)->pluck('id')->toArray();

            $translations = QuestionTranslation::with('language.voices')->whereNotNull('question_translation')
                ->whereIn('language_id', $languages)
                // ->take(1)
                ->orderBy('id', 'asc')
                ->get();

            // Calculate total fields to convert
            $total = $translations->count();
            Log::info("start translation", [
                "total" => $total,
                "language" => $languages,
                "translations" => $translations
            ]);
            $progress = [
                'total' => $total,
                'completed' => 0,
                'status' => 'running',
                'message' => 'Starting audio conversion...'
            ];
            $this->updateProgress($progress);

            $tts = new AzureTTSService();

            foreach ($translations as $translation) {
                if ($this->shouldStop()) {
                    $this->updateProgress($progress, 'stopped', 'Audio conversion stopped by user');
                    return;
                }
                if (!empty($translation->question_audio) && !empty($translation->a_audio) && !empty($translation->b_audio) && !empty($translation->c_audio) && !empty($translation->answer_explanation_audio)) {
                    $progress['completed']++;
                    $this->updateProgress($progress);
                    continue;
                }

                $fields = [
                    'question' => $translation->question_translation ?? null,
                    'a' => $translation->a_translation ?? null,
                    'b' => $translation->b_translation ?? null,
                    'c' => $translation->c_translation ?? null,
                    'd' => $translation->d_translation ?? null,
                    'answer_explanation' => $translation->answer_explanation_translation ?? null
                ];

                $converted = false;

                foreach ($fields as $field => $text) {
                    if ($this->shouldStop()) {
                        $this->updateProgress($progress, 'stopped', 'Audio conversion stopped by user');
                        return;
                    }

                    if (empty($text)) {
                        continue;
                    }

                    $audioField = "{$field}_audio";
                    if (!empty($translation->$audioField)) {
                        continue;
                    }

                    // Convert text to speech
                    $audioContent = $tts->convertToSpeech($text, $translation->language);

                    log::info("convert to speach", [
                        "field name" => $audioField,
                    ]);

                    if (is_array($audioContent) && $audioContent['status'] === false) {
                        $progress['status'] = "error";
                        $progress['message'] = "TTS conversion failed. The reason is: {$audioContent['message']}";
                        $this->updateProgress($progress);
                        break;
                    }
                    if (!is_array($audioContent)) {
                        // Save audio file
                        $path = public_path('audios');
                        $fileName = "{$field}_" . time() . '.mp3';
                        file_put_contents($path . '/' . $fileName, $audioContent);

                        // Update translation record
                        $translation->update([$audioField => $fileName]);
                        $converted = true;

                        // Prevent rate limiting
                        usleep(500000);
                    }
                }

                // ✅ Now mark progress if anything was done
                if ($converted) {
                    $progress['completed']++;
                    $progress['message'] = "Converting {$field} for question {$translation->question_id} - {$translation->id} - {$translation->language->name}";
                    $this->updateProgress($progress);
                }
            }

            $this->updateProgress($progress, 'completed', 'Audio conversion completed successfully');
        } catch (\Exception $e) {
            Log::error('Audio conversion job failed: ' . $e->getMessage());
            $this->updateProgress($progress, 'error', 'Audio conversion failed: ' . $e->getMessage());
        }
    }

    private function shouldStop(): bool
    {
        return Cache::get('tts_immediate_stop') ||
            Setting::first()?->tts_stopped ||
            Cache::get('tts_stop_flag') ||
            Cache::get('tts_force_stop');
    }

    // private function shouldUpdateAudio(QuestionTranslation $translation, string $audioField): bool
    // {
    //     // If audio doesn't exist, should convert
    //     if (!$translation->$audioField) {
    //         return true;
    //     }

    //     // If text was updated after audio was created, should reconvert
    //     $audioPath = Storage::path('public/' . $translation->$audioField);
    //     if (!file_exists($audioPath)) {
    //         return true;
    //     }

    //     return $translation->updated_at > filemtime($audioPath);
    // }

    // private function translateFields(Question $question, Language $language): array|false
    // {

    // }

    private function updateProgress(array &$progress, ?string $status = null, ?string $message = null): void
    {
        // Log::info('updateProgress', ['progress' => $progress, 'status' => $status, 'message' => $message]);
        if ($status) {
            $progress['status'] = $status;
        }
        if ($message) {
            $progress['message'] = $message;
        }
        Cache::put('tts_progress', $progress, 3600);
    }
}