<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuestionLanguageController;

Route::middleware(['auth', 'role:admin'])->as('admin.')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('quiz', QuizController::class)->except('create', 'store', 'show', 'destroy', 'edit', 'update');
    Route::resource('quiz.question', QuestionController::class);
    Route::resource('quiz.question.language', QuestionLanguageController::class);
    Route::post('import-question/{quiz}', [QuestionController::class, 'importQuestion'])->name('question.import.file');
    Route::get('destroy-question/{quiz}', [QuestionController::class, 'destroyAll'])->name('quiz.question.destroy.all');
    Route::resource('users', UserController::class);
});

require __DIR__ . '/auth.php';
