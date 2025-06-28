<?php

use App\Models\Setting;
use App\Jobs\SimpleTestJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use App\Jobs\BulkTranslateQuestionsJob;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\LenguageController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ContactUsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\QuestionLanguageController;
use App\Http\Controllers\Admin\TextToSpeechController;
use App\Http\Controllers\Admin\QuestionTranslationController;

Route::middleware(['auth', 'role:admin'])->as('admin.')->group(function () {
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Quiz and Question Management
    Route::resource('quiz', QuizController::class)->except('create', 'store', 'show', 'destroy');
    Route::resource('quiz.question', QuestionController::class);
    Route::resource('quiz.question.language', QuestionLanguageController::class);
    Route::post('import-question/{quiz}', [QuestionController::class, 'importQuestion'])->name('question.import.file');
    Route::get('destroy-question/{quiz}', [QuestionController::class, 'destroyAll'])->name('quiz.question.destroy.all');
    Route::resource('users', UserController::class);

    //Lenguage
    Route::group(['prefix' => 'lenguage', 'as' => 'lenguage.'], function () {
        Route::get('/', [LenguageController::class, 'index'])->name('index');
        Route::post('/update/{id}', [LenguageController::class, 'update'])->name('update')->withoutMiddleware(['auth']);
    });

    // Contact Us
    Route::get('contact-us', [ContactUsController::class, 'index'])->name('contact-us.index');

    // Settings
    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        Route::get('index', [SettingController::class, 'index'])->name('index');
        Route::post('update', [SettingController::class, 'update'])->name('update');
        Route::post('reset-default', [SettingController::class, 'resetDefault'])->name('resetDefault');
    });

    // Translation
    Route::group(['prefix' => 'translations', 'as' => 'translations.'], function () {
        Route::get('/', [TranslationController::class, 'index'])->name('index');
        // Translation Api
        Route::get('create-translation', [TranslationController::class, 'createTranslation'])->name('createTranslation');
        Route::post('translation/start', [TranslationController::class, 'translateAll'])->name('start');
        Route::get('translation/progress', [TranslationController::class, 'getProgress'])->name('progress');
        Route::get('translation/progress/{question_id}', [TranslationController::class, 'getQuestionProgress'])->name('question.progress');
        Route::post('translation/stop', [TranslationController::class, 'stopTranslation'])->name('stop');

        // Text to Speech routes
        Route::get('create-tts', [TextToSpeechController::class, 'index'])->name('createTts');
        Route::post('text-to-speech/start', [TextToSpeechController::class, 'convertAll'])->name('tts.start');
        Route::get('text-to-speech/progress', [TextToSpeechController::class, 'getProgress'])->name('tts.progress');
        Route::post('text-to-speech/stop', [TextToSpeechController::class, 'stopConversion'])->name('tts.stop');
    });
});

Route::post('/test-queue', function (Request $request) {
    try {
        $request->validate([
            'api_key' => 'required|string',
            'source_language' => 'required|string|max:10',
            'batch_size' => 'integer|min:1|max:10'
        ]);

        DB::transaction(function () use ($request) {
            $setting = Setting::first();
            if ($setting) {
                $setting->update(['translation_stopped' => false]);
            }
            // Cache::forget('translation_stop_flag');
            // Cache::forget('translation_force_stop');
            // Cache::forget('translation_stopped_at');
            // Cache::forget('translation_immediate_stop');
            // Cache::forget('translation_progress');
        });

        //   Queue::clear('database');

        Log::info('Before dispatch');
        // SimpleTestJob::dispatch();
        BulkTranslateQuestionsJob::dispatch($request->api_key, $request->source_language, $request->batch_size);
        Log::info('After dispatch');

        return response()->json([
            'success' => true,
            'message' => 'Translation process started.'
        ]);
    } catch (\Exception $e) {
        Log::error('Error in test-queue: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to dispatch job'], 500);
    }
})->name('test-queue');

require __DIR__ . '/auth.php';
