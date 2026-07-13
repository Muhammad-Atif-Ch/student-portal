<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BulkTranslateQuestionsJob;
use App\Models\Language;
use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Models\Setting;
use App\Services\AzureTranslation\AzureTranslatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{
    private const FIELDS = ['question', 'a', 'b', 'c', 'd', 'answer_explanation'];

    public function index(Request $request)
    {
        $where = collect($request->only(['quiz_id', 'question_id', 'language_id', 'type']))
            ->filter(fn ($value) => ! is_null($value))
            ->toArray();

        $translations = QuestionTranslation::with('quiz:id', 'question:id', 'language:id,name')
            ->where($where)
            ->paginate(100);

        $languages = Language::get();

        return view('backend.translations.index', compact('translations', 'languages'));
    }

    public function createTranslation()
    {
        $languages = Language::where('status', 'active')->get();
        $totalQuestions = Question::whereNotNull('question')->where('question', '!=', '')->count();
        $translatedQuestions = QuestionTranslation::select('question_id')->distinct()->count();

        return view('backend.translations.create_translation', compact('languages', 'totalQuestions', 'translatedQuestions'));
    }

    public function translateAll(Request $request)
    {

        if (empty(config('services.azure_translator.key'))) {
            return response()->json([
                'error' => 'Azure Translator API key is not configured. Set AZURE_TRANSLATOR_KEY in .env.',
            ], 500);
        }

        try {
            $this->resetFlags('translation');

            BulkTranslateQuestionsJob::dispatch();

            return response()->json([
                'success' => true,
                'message' => 'Translation process started.',
            ]);
        } catch (\Exception $e) {
            Log::error('Translation error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to start translation'], 500);
        }
    }

    public function retranslateField(Request $request, QuestionTranslation $translation, AzureTranslatorService $translator)
    {
        $request->validate([
            'field' => 'required|in:'.implode(',', self::FIELDS),
        ]);

        if (empty(config('services.azure_translator.key'))) {
            return response()->json([
                'error' => 'Azure Translator API key is not configured. Set AZURE_TRANSLATOR_KEY in .env.',
            ], 500);
        }

        $field = $request->input('field');
        $translation->loadMissing('question', 'language');
        $question = $translation->question;
        $language = $translation->language;

        if (! $question || ! $language) {
            return response()->json(['error' => 'Related question or language not found.'], 404);
        }

        $sourceFields = $translator->resolveSourceFields($question, $language);
        $sourceText = $sourceFields[$field] ?? null;

        if (empty($sourceText)) {
            return response()->json(['error' => 'Source text for this field is empty, nothing to translate.'], 422);
        }

        $translated = $translator->translateOne($sourceText, $language->code);

        if ($translated === false) {
            return response()->json(['error' => 'Azure Translate request failed. Check logs for details.'], 500);
        }

        $translation->update(["{$field}_translation" => $translated]);

        return response()->json([
            'success' => true,
            'field' => $field,
            'translation' => $translated,
        ]);
    }

    public function getProgress()
    {
        return response()->json($this->buildProgressResponse('translation_progress'));
    }

    /**
     * Authoritative, DB-backed report: which language is complete, which
     * has partial/errored rows, and how many questions are untouched.
     * Unlike getProgress() (a live snapshot of the currently running job,
     * held in cache) this works at any time - after a job finishes, after
     * a restart, or after several resumed runs - because it reads the
     * status column persisted on each question_translations row rather
     * than relying on in-memory counters.
     */
    public function getReport()
    {
        $totalQuestions = Question::whereNotNull('question')->where('question', '!=', '')->count();
        $languages = Language::where('status', 1)->get();

        $counts = QuestionTranslation::selectRaw('language_id, status, count(*) as count')
            ->groupBy('language_id', 'status')
            ->get()
            ->groupBy('language_id');

        $report = $languages->map(function ($language) use ($totalQuestions, $counts) {
            $byStatus = $counts->get($language->id, collect())->pluck('count', 'status');

            $completed = (int) $byStatus->get('completed', 0);
            $partial = (int) $byStatus->get('partial', 0);
            $errored = (int) $byStatus->get('error', 0);
            $notStarted = max(0, $totalQuestions - $completed - $partial - $errored);

            return [
                'language_id' => $language->id,
                'language' => $language->name,
                'total' => $totalQuestions,
                'completed' => $completed,
                'partial' => $partial,
                'errored' => $errored,
                'not_started' => $notStarted,
            ];
        });

        // Rows the admin needs to look at: anything not fully completed.
        $needsAttention = QuestionTranslation::with('question:id', 'language:id,name')
            ->whereIn('status', ['partial', 'error'])
            ->orderByDesc('updated_at')
            ->paginate(100);

        return response()->json([
            'by_language' => $report,
            'needs_attention' => $needsAttention,
        ]);
    }

    public function getQuestionProgress($question_id)
    {
        $progress = Cache::get("translation_progress_question_{$question_id}", [
            'total' => 0,
            'completed' => 0,
            'status' => 'idle',
            'message' => '',
            'question_id' => $question_id,
        ]);

        $percentage = $progress['total'] > 0
            ? round(($progress['completed'] / $progress['total']) * 100, 2)
            : 0;

        return response()->json([
            'progress' => $progress,
            'percentage' => max(0, min(100, $percentage)),
        ]);
    }

    public function stopTranslation()
    {
        try {
            Setting::first()?->update(['translation_stopped' => true]);

            // Merge onto whatever's already there instead of overwriting,
            // so completed/partial/errored/by_language counts from the run
            // so far aren't wiped out - only the status/message change.
            $current = Cache::get('translation_progress', []);
            Cache::put('translation_progress', array_merge($current, [
                'status' => 'stopped',
                'message' => 'Translation process stopped by user',
            ]), 3600);

            // A single cache flag is enough - the job polls this on the hot
            // path, so it needs to stay cheap. The Setting column above is
            // kept purely for persistence/audit, not for polling.
            Cache::put('translation_stop_flag', true, 3600);

            return response()->json([
                'success' => true,
                'message' => 'Translation process stopped successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Stop translation error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to stop translation'], 500);
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

        // 'processed' counts every pair the job has looked at, which is
        // what should drive the percentage bar - 'completed' alone would
        // make the bar stall on any run with partial/errored pairs.
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
