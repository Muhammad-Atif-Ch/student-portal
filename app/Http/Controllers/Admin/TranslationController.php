<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Models\Language;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\QuestionTranslation;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Jobs\BulkTranslateQuestionsJob;

class TranslationController extends Controller
{
  public function index(Request $request)
  {
    $where = collect($request->only(['quiz_id', 'question_id', 'language_id', 'type']))
      ->filter(fn($value) => !is_null($value))
      ->toArray();

    $translations = QuestionTranslation::with('quiz:id', 'question:id', 'language:id,name')->where($where)->paginate(100);
    $languages = Language::get();
    return view('backend.translations.index', compact('translations', 'languages'));
  }

  public function createTranslation()
  {
    $languages = Language::where('status', 'active')->get();
    $totalQuestions = Question::count();
    $translatedQuestions = QuestionTranslation::select('question_id')->distinct()->count();

    return view('backend.translations.create_translation', compact('languages', 'totalQuestions', 'translatedQuestions'));
  }

  public function translateAll(Request $request)
  {
    try {
      $request->validate([
        'api_key' => 'required|string',
        'source_language' => 'required|string|max:10',
        'batch_size' => 'integer|min:1|max:10'
      ]);

      // Reset translation flags
      DB::transaction(function () {
        $setting = Setting::first();
        if ($setting) {
          $setting->update(['translation_stopped' => false]);
        }

        Cache::forget('translation_stop_flag');
        Cache::forget('translation_force_stop');
        Cache::forget('translation_stopped_at');
        Cache::forget('translation_immediate_stop');
        Cache::forget('translation_progress');
      });

      // Dispatch translation job
      dispatch(new BulkTranslateQuestionsJob(
        $request->api_key,
        $request->source_language,
        $request->batch_size
      ));

      return response()->json([
        'success' => true,
        'message' => 'Translation process started.'
      ]);
    } catch (\Exception $e) {
      Log::error('Translation error: ' . $e->getMessage());
      return response()->json(['error' => 'Failed to start translation'], 500);
    }
  }

  public function getProgress()
  {
    $progress = Cache::get('translation_progress', [
      'total' => 0,
      'completed' => 0,
      'current_question' => 0,
      'current_language' => 0,
      'status' => 'idle',
      'logs' => []
    ]);

    $percentage = $progress['total'] > 0
      ? round(($progress['completed'] / $progress['total']) * 100, 2)
      : 0;

    $response = [
      'progress' => $progress,
      'percentage' => max(0, min(100, $percentage))
    ];

    return response()->json($response);
  }

  public function getQuestionProgress($question_id)
  {
    $progress = Cache::get("translation_progress_question_{$question_id}", [
      'total' => 0,
      'completed' => 0,
      'status' => 'idle',
      'message' => '',
      'question_id' => $question_id
    ]);

    $percentage = $progress['total'] > 0
      ? round(($progress['completed'] / $progress['total']) * 100, 2)
      : 0;

    return response()->json([
      'progress' => $progress,
      'percentage' => max(0, min(100, $percentage))
    ]);
  }

  public function stopTranslation()
  {
    try {
      DB::transaction(function () {
        Setting::first()?->update(['translation_stopped' => true]);

        Cache::put('translation_progress', [
          'total' => 0,
          'completed' => 0,
          'status' => 'stopped',
          'message' => 'Translation process stopped by user'
        ], 3600);

        Cache::put('translation_stop_flag', true, 3600);
        Cache::put('translation_force_stop', true, 3600);
        Cache::put('translation_immediate_stop', true, 3600);
      });

      return response()->json([
        'success' => true,
        'message' => 'Translation process stopped successfully.'
      ]);
    } catch (\Exception $e) {
      Log::error('Stop translation error: ' . $e->getMessage());
      return response()->json(['error' => 'Failed to stop translation'], 500);
    }
  }
}