<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\TextToSpeechConversionJob;
use App\Models\Language;
use App\Models\QuestionTranslation;
use App\Models\Setting;
use App\Services\AzureTextToSpeech\AzureTTSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TextToSpeechController extends Controller
{
    private const FIELDS = ['question', 'a', 'b', 'c', 'd', 'answer_explanation'];

    public function index()
    {
        return view('backend.translations.create_tts');
    }

    public function reconvertField(Request $request, QuestionTranslation $translation, AzureTTSService $tts)
    {
        $request->validate([
            'field' => 'required|in:'.implode(',', self::FIELDS),
        ]);

        $field = $request->input('field');
        $translation->loadMissing('language.voices');
        $language = $translation->language;

        if (! $language) {
            return response()->json(['error' => 'Language not found.'], 404);
        }

        $text = $translation->{"{$field}_translation"};
        if (empty($text)) {
            return response()->json(['error' => 'Translation text for this field is empty. Translate it first.'], 422);
        }

        $audioContent = $tts->convertToSpeech($text, $language);

        if (is_array($audioContent) && ($audioContent['status'] ?? false) === false) {
            return response()->json([
                'error' => $audioContent['message'] ?? 'TTS conversion failed. Check logs for details.',
            ], 500);
        }

        $audioField = "{$field}_audio";
        $oldFile = $translation->{$audioField};
        if ($oldFile) {
            $oldPath = public_path('audios/'.$oldFile);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $fileName = "{$field}_".time().'_'.$translation->id.'.mp3';
        file_put_contents(public_path('audios/'.$fileName), $audioContent);

        $translation->update([$audioField => $fileName]);

        return response()->json([
            'success' => true,
            'field' => $field,
            'audio' => $fileName,
            'audio_url' => asset('audios/'.$fileName),
        ]);
    }

    public function convertAll(Request $request)
    {
        try {
            $this->resetFlags('tts');

            TextToSpeechConversionJob::dispatch();

            return response()->json([
                'success' => true,
                'message' => 'Audio conversion process started.',
            ]);
        } catch (\Exception $e) {
            Log::error('TTS conversion error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to start audio conversion'], 500);
        }
    }

    public function getProgress()
    {
        return response()->json($this->buildProgressResponse('tts_progress'));
    }

    /**
     * DB-backed report: which language has full audio, which is partial,
     * and which rows still need attention. Works at any time, not only
     * while a job is running.
     */
    public function getReport()
    {
        $languages = Language::where('status', 1)->get();

        $rows = QuestionTranslation::whereIn('language_id', $languages->pluck('id'))->get();

        $byLanguage = $languages->map(function ($language) use ($rows) {
            $langRows = $rows->where('language_id', $language->id);
            $stats = ['completed' => 0, 'partial' => 0, 'errored' => 0, 'skipped' => 0];

            foreach ($langRows as $row) {
                $status = $this->classifyTtsStatus($row);
                $stats[$status]++;
            }

            return [
                'language_id' => $language->id,
                'name' => $language->name,
                'total' => $langRows->count(),
                'completed' => $stats['completed'],
                'partial' => $stats['partial'],
                'errored' => $stats['errored'],
                'skipped' => $stats['skipped'],
            ];
        });

        $needsAttention = QuestionTranslation::with('question:id', 'language:id,name')
            ->whereIn('language_id', $languages->pluck('id'))
            ->get()
            ->filter(fn ($row) => in_array($this->classifyTtsStatus($row), ['partial', 'errored']))
            ->sortByDesc('updated_at')
            ->take(100)
            ->map(fn ($row) => [
                'question_id' => $row->question_id,
                'language' => $row->language,
                'language_id' => $row->language_id,
                'status' => $this->classifyTtsStatus($row),
                'error' => $row->error,
            ])
            ->values();

        return response()->json([
            'by_language' => $byLanguage,
            'needs_attention' => ['data' => $needsAttention],
        ]);
    }

    public function stopConversion()
    {
        try {
            Setting::first()?->update(['tts_stopped' => true]);

            $current = Cache::get('tts_progress', []);
            Cache::put('tts_progress', array_merge($current, [
                'status' => 'stopped',
                'message' => 'Audio conversion process stopped by user',
            ]), 3600);

            Cache::put('tts_stop_flag', true, 3600);

            return response()->json([
                'success' => true,
                'message' => 'Audio conversion process stopped successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Stop TTS conversion error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to stop audio conversion'], 500);
        }
    }

    private function resetFlags(string $prefix): void
    {
        DB::transaction(function () use ($prefix) {
            Setting::first()?->update(["{$prefix}_stopped" => false]);
            Cache::forget("{$prefix}_stop_flag");
            Cache::forget("{$prefix}_progress");
        });
    }

    private function buildProgressResponse(string $cacheKey): array
    {
        $progress = Cache::get($cacheKey, [
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

    private function classifyTtsStatus(QuestionTranslation $row): string
    {
        $hasText = false;
        $missingAudio = false;

        foreach (self::FIELDS as $field) {
            $text = $row->{"{$field}_translation"};
            if (empty($text)) {
                continue;
            }

            $hasText = true;

            if (empty($row->{"{$field}_audio"})) {
                $missingAudio = true;
            }
        }

        if (! $hasText) {
            return 'skipped';
        }

        if ($row->status === 'error') {
            return 'errored';
        }

        return $missingAudio ? 'partial' : 'completed';
    }
}
