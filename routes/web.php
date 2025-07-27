<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ContactUsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\TextToSpeechController;

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
    Route::post('import-question/{quiz}', [QuestionController::class, 'importQuestion'])->name('question.import.file');
    Route::get('destroy-question/{quiz}', [QuestionController::class, 'destroyAll'])->name('quiz.question.destroy.all');
    Route::resource('users', UserController::class);

    //Language
    Route::group(['prefix' => 'language', 'as' => 'language.'], function () {
        Route::get('/', [LanguageController::class, 'index'])->name('index');
        Route::get('/create', [LanguageController::class, 'create'])->name('create');
        Route::post('/store', [LanguageController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [LanguageController::class, 'edit'])->name('edit');
        Route::patch('/update/{id}', [LanguageController::class, 'update'])->name('update');
        Route::post('/update-status', [LanguageController::class, 'status'])->name('update.status');
        Route::delete('/destroy/{language}', [LanguageController::class, 'destroy'])->name('destroy');
    });

    //Custom Notification
    Route::group(['prefix' => 'notification', 'as' => 'notification.'], function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/create', [NotificationController::class, 'create'])->name('create');
        Route::post('/store', [NotificationController::class, 'store'])->name('store');
    });

    // Contact Us
    Route::get('contact-us', [ContactUsController::class, 'index'])->name('contact-us.index');

    // Settings
    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        Route::get('index', [SettingController::class, 'index'])->name('index');
        Route::post('update', [SettingController::class, 'update'])->name('update');
        Route::post('reset-default', [SettingController::class, 'resetDefault'])->name('resetDefault');
        Route::get('app-image', [SettingController::class, 'appImage'])->name('appImage');
        Route::post('app-image-update', [SettingController::class, 'appImageUpdate'])->name('appImage.update');
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

require __DIR__ . '/auth.php';
