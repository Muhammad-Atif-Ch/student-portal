<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Jobs\TextToSpeechConversionJob;

class TextToSpeechController extends Controller
{
  public function index()
  {
    return view('backend.translations.create_tts');
  }

  public function convertAll(Request $request)
  {
    try {
      $request->validate([
        'batch_size' => 'integer|min:1|max:10'
      ]);

      // Reset conversion flags
      DB::transaction(function () {
        $setting = Setting::first();
        if ($setting) {
          $setting->update(['tts_stopped' => false]);
        }

        Cache::forget('tts_stop_flag');
        Cache::forget('tts_force_stop');
        Cache::forget('tts_immediate_stop');
        Cache::forget('tts_progress');
      });

      // Dispatch conversion job
      dispatch(new TextToSpeechConversionJob(
        $request->batch_size ?? 5
      ));

      return response()->json([
        'success' => true,
        'message' => 'Audio conversion process started.'
      ]);
    } catch (\Exception $e) {
      Log::error('TTS conversion error: ' . $e->getMessage());
      return response()->json(['error' => 'Failed to start audio conversion'], 500);
    }
  }

  public function getProgress()
  {
    $progress = Cache::get('tts_progress', [
      'total' => 0,
      'completed' => 0,
      'status' => 'idle',
      'message' => ''
    ]);

    $percentage = $progress['total'] > 0
      ? round(($progress['completed'] / $progress['total']) * 100, 2)
      : 0;

    $response = [
      'progress' => $progress,
      'percentage' => max(0, min(100, $percentage))
    ];

    if ($progress['status'] === 'error' && isset($progress['error'])) {
      $response['error'] = $progress['error'];
    }

    return response()->json($response);
  }

  public function stopConversion()
  {
    try {
      DB::transaction(function () {
        Setting::first()?->update(['tts_stopped' => true]);

        Cache::put('tts_progress', [
          'total' => 0,
          'completed' => 0,
          'status' => 'stopped',
          'message' => 'Audio conversion process stopped by user'
        ], 3600);

        Cache::put('tts_stop_flag', true, 3600);
        Cache::put('tts_force_stop', true, 3600);
        Cache::put('tts_immediate_stop', true, 3600);
      });

      return response()->json([
        'success' => true,
        'message' => 'Audio conversion process stopped successfully.'
      ]);
    } catch (\Exception $e) {
      Log::error('Stop TTS conversion error: ' . $e->getMessage());
      return response()->json(['error' => 'Failed to stop audio conversion'], 500);
    }
  }
}