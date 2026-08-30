<?php

use App\Http\Controllers\Admin\ContactUsController as AdminContactUsController;
use App\Http\Controllers\Admin\CpcCaseStudyController;
use App\Http\Controllers\Admin\CpcExamController;
use App\Http\Controllers\Admin\CpcQuestionController;
use App\Http\Controllers\Admin\CpcTranslation\CpcCaseStudyTranslationController;
use App\Http\Controllers\Admin\CpcTranslation\CpcQuestionTranslationController;
use App\Http\Controllers\Admin\CpcTypeController;
use App\Http\Controllers\Admin\ClientAppController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamTypeController;
use App\Http\Controllers\Admin\ExamPoolRuleController;
use App\Http\Controllers\Admin\ExamTypeTargetTypeController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LanguageVoiceController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TechnicalDictionaryController;
use App\Http\Controllers\Admin\TechnicalDictionaryTranslationController;
use App\Http\Controllers\Admin\TextToSpeechController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\TranslationGlossaryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Frontend\ContactUsController as FrontendContactUsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->as('admin.')->group(function () {
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard
    Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/language-usage/filter', [DashboardController::class, 'filterLanguageUsage'])->name('filter.languageUsage');
    });

    // Exam Management
    Route::group(['prefix' => 'exam', 'as' => 'exam.'], function () {
        Route::resource('type', ExamTypeController::class);
        Route::resource('target-type', ExamTypeTargetTypeController::class)->except('show');
        Route::resource('pool-rule', ExamPoolRuleController::class)->except('show');
    });

    // Quiz and Question Management
    Route::resource('quiz', QuizController::class)->except('create', 'store', 'show', 'destroy');
    Route::group(['prefix' => 'quiz', 'as' => 'quiz.question.'], function () {
        Route::get('{quiz}/question/destroy', [QuestionController::class, 'destroyAll'])->name('destroy.all');
        Route::delete('question/{question}/remove-image', [QuestionController::class, 'removeImage'])->name('removeImage');
        Route::get('/question/sample/download', [QuestionController::class, 'downloadSample'])->name('sample.download');
        Route::post('{quiz}/question/import', [QuestionController::class, 'importQuestion'])->name('import.file');
        Route::get('{quiz}/question/export', [QuestionController::class, 'exportQuestion'])->name('export');
    });
    Route::resource('quiz.question', QuestionController::class);

    // CPC Management
    Route::group(['prefix' => 'cpc', 'as' => 'cpc.'], function () {
        Route::resource('question', CpcQuestionController::class)->except('show');
        Route::get('question-sample/download', [CpcQuestionController::class, 'downloadSample'])->name('question.sample.download');
        Route::post('question/import/file', [CpcQuestionController::class, 'importQuestion'])->name('question.import.file');
        Route::resource('type', CpcTypeController::class)->except('show');
        Route::resource('exam', CpcExamController::class)->except('show');

        Route::resource('case-study', CpcCaseStudyController::class)->parameters(['case-study' => 'caseStudy']);
        Route::group(['prefix' => 'case-study/{caseStudy}', 'as' => 'case-study.'], function () {
            Route::post('translate-all', [CpcCaseStudyTranslationController::class, 'translateAll'])->name('translate-all');
            Route::post('title/translate/{language}', [CpcCaseStudyTranslationController::class, 'translateTitle'])->name('title.translate');
            Route::post('blocks/{block}/translate/{language}', [CpcCaseStudyTranslationController::class, 'translateBlock'])->name('blocks.translate');
            Route::post('blocks/{block}/generate-audio/{language}', [CpcCaseStudyTranslationController::class, 'generateAudioForBlock'])->name('blocks.audio');
        });

        Route::group(['prefix' => 'translation', 'as' => 'translation.'], function () {
            Route::get('/', [CpcCaseStudyTranslationController::class, 'translationIndex'])->name('index');
            Route::get('{caseStudy}/questions', [CpcCaseStudyTranslationController::class, 'translationQuestions'])->name('questions');

            Route::group(['prefix' => '{caseStudy}/questions', 'as' => 'questions.'], function () {
                Route::post('translate-all', [CpcQuestionTranslationController::class, 'translateAllForCaseStudy'])->name('translate-all');
                Route::post('generate-audio-all', [CpcQuestionTranslationController::class, 'generateAudioAllForCaseStudy'])->name('audio-all');
                Route::post('{question}/translate/{language}', [CpcQuestionTranslationController::class, 'translate'])->name('translate');
                Route::post('{question}/generate-audio/{language}', [CpcQuestionTranslationController::class, 'generateAudio'])->name('audio');
                Route::post('{question}/text/translate/{language}', [CpcQuestionTranslationController::class, 'translateQuestionText'])->name('text.translate');
                Route::post('{question}/text/generate-audio/{language}', [CpcQuestionTranslationController::class, 'generateAudioForQuestionText'])->name('text.audio');
                Route::post('{question}/explanation/translate/{language}', [CpcQuestionTranslationController::class, 'translateExplanation'])->name('explanation.translate');
                Route::post('{question}/explanation/generate-audio/{language}', [CpcQuestionTranslationController::class, 'generateAudioForExplanation'])->name('explanation.audio');
                Route::post('{question}/options/{option}/translate/{language}', [CpcQuestionTranslationController::class, 'translateOption'])->name('options.translate');
                Route::post('{question}/options/{option}/generate-audio/{language}', [CpcQuestionTranslationController::class, 'generateAudioForOption'])->name('options.audio');
            });
        });
    });

    // Technical Dictionary
    Route::group(['prefix' => 'technical-dictionary', 'as' => 'technical-dictionary.'], function () {
        Route::get('/', [TechnicalDictionaryController::class, 'index'])->name('index');
        Route::get('/create', [TechnicalDictionaryController::class, 'create'])->name('create');
        Route::post('/store', [TechnicalDictionaryController::class, 'store'])->name('store');
        Route::get('/{technicalDictionary}/edit', [TechnicalDictionaryController::class, 'edit'])->name('edit');
        Route::put('/{technicalDictionary}', [TechnicalDictionaryController::class, 'update'])->name('update');
        Route::delete('/{technicalDictionary}', [TechnicalDictionaryController::class, 'destroy'])->name('destroy');
        Route::post('/{technicalDictionary}/regenerate/{language}', [TechnicalDictionaryTranslationController::class, 'regenerate'])->name('regenerate');
    });

    // Users
    Route::resource('users', UserController::class);

    // Language
    Route::group(['prefix' => 'language', 'as' => 'language.'], function () {
        Route::get('/', [LanguageController::class, 'index'])->name('index');
        Route::get('/create', [LanguageController::class, 'create'])->name('create');
        Route::post('/store', [LanguageController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [LanguageController::class, 'edit'])->name('edit');
        Route::patch('/update/{id}', [LanguageController::class, 'update'])->name('update');
        Route::post('/update-status', [LanguageController::class, 'status'])->name('update.status');
        Route::post('/update-show-status', [LanguageController::class, 'showStatus'])->name('update.show.status');
        Route::delete('/destroy/{language}', [LanguageController::class, 'destroy'])->name('destroy');

        // Language Voice
        Route::group(['prefix' => '{language}/language-voice', 'as' => 'voice.'], function () {
            Route::get('/', [LanguageVoiceController::class, 'index'])->name('index');
            Route::get('/create', [LanguageVoiceController::class, 'create'])->name('create');
            Route::post('/store', [LanguageVoiceController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [LanguageVoiceController::class, 'edit'])->name('edit');
            Route::patch('/update/{id}', [LanguageVoiceController::class, 'update'])->name('update');
            Route::delete('/destroy/{languageVoice}', [LanguageVoiceController::class, 'destroy'])->name('destroy');
        });
    });

    // Custom Notification
    Route::group(['prefix' => 'notification', 'as' => 'notification.'], function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/create', [NotificationController::class, 'create'])->name('create');
        Route::post('/store', [NotificationController::class, 'store'])->name('store');
    });

    // Contact Us
    Route::group(['prefix' => 'contact-us', 'as' => 'contact-us.'], function () {
        Route::get('/', [AdminContactUsController::class, 'index'])->name('index');
        Route::post('update/{id}', [AdminContactUsController::class, 'updateStatus'])->name('update');
        Route::get('show/{contact_us}', [AdminContactUsController::class, 'show'])->name('show');
    });

    // Settings
    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        // Route::get('index', [SettingController::class, 'index'])->name('index');
        Route::post('update', [SettingController::class, 'update'])->name('update');
        Route::post('reset-default', [SettingController::class, 'resetDefault'])->name('resetDefault');
        Route::get('api-settings', [SettingController::class, 'apiSettings'])->name('apiSettings');
        Route::post('api-settings-update', [SettingController::class, 'apiSettingsUpdate'])->name('apiSettings.update');
    });

    // Client Apps (image CRUD for the 3 apps consuming the Translation/TTS APIs)
    Route::group(['prefix' => 'client-apps', 'as' => 'client-apps.'], function () {
        Route::get('/', [ClientAppController::class, 'index'])->name('index');
        Route::post('update/{clientApp}', [ClientAppController::class, 'update'])->name('update');
    });

    // Translation
    Route::group(['prefix' => 'translations', 'as' => 'translations.'], function () {
        // Translation
        Route::get('/', [TranslationController::class, 'index'])->name('index');
        Route::get('create', [TranslationController::class, 'create'])->name('create');
        Route::post('combined/start', [TranslationController::class, 'combinedStart'])->name('combined.start');
        Route::get('combined/progress', [TranslationController::class, 'combinedProgress'])->name('combined.progress');
        Route::post('combined/stop', [TranslationController::class, 'combinedStop'])->name('combined.stop');
        Route::get('combined/report', [TranslationController::class, 'getReport'])->name('combined.report');
        Route::post('{translation}/retranslate', [TranslationController::class, 'retranslateField'])->name('combined.retranslate-field');
        // Text to Speech
        Route::post('{translation}/reconvert', [TextToSpeechController::class, 'reconvertField'])->name('tts.reconvert-field');
        // Glossary

        Route::resource('glossary', TranslationGlossaryController::class)->except('show');
        Route::group(['prefix' => 'glossary', 'as' => 'glossary.'], function () {
            Route::get('glossary/destroy-all', [TranslationGlossaryController::class, 'destroyAll'])->name('destroy.all');
            Route::post('glossary/import', [TranslationGlossaryController::class, 'importTranslationGlossary'])->name('import.file');
        });
    });
});

/* Frontend */

// Contact Us
Route::group(['as' => 'frontend.'], function () {
    Route::group(['prefix' => 'contact', 'as' => 'contact.'], function () {
        Route::get('/', [FrontendContactUsController::class, 'index'])->name('index');
        Route::post('store', [FrontendContactUsController::class, 'store'])->name('store');
    });
});

require __DIR__.'/auth.php';
