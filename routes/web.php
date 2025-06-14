<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\LenguageController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuestionLanguageController;

Route::middleware(['auth', 'role:admin'])->as('admin.')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('quiz', QuizController::class)->except('create', 'store', 'show', 'destroy');
    Route::resource('quiz.question', QuestionController::class);
    Route::resource('quiz.question.language', QuestionLanguageController::class);
    Route::post('import-question/{quiz}', [QuestionController::class, 'importQuestion'])->name('question.import.file');
    Route::get('destroy-question/{quiz}', [QuestionController::class, 'destroyAll'])->name('quiz.question.destroy.all');
    Route::resource('users', UserController::class);
    Route::group(['prefix' => 'lenguage', 'as' => 'lenguage.'], function () {
        Route::get('/', [LenguageController::class, 'index'])->name('index');
        Route::post('/update/{id}', [LenguageController::class, 'update'])->name('update')->withoutMiddleware(['auth']);
    });

    // Settings
    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        Route::get('index', [SettingController::class, 'index'])->name('index');
        Route::post('update', [SettingController::class, 'update'])->name('update');
        Route::post('reset-default', [SettingController::class, 'resetDefault'])->name('resetDefault');
    });
});

require __DIR__ . '/auth.php';
