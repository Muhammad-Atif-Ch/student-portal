<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\UserController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::get('user/device-register', [UserController::class, 'index'])->withoutMiddleware('device.check');

Route::group(['prefix' => 'quiz'], function () {
    Route::get('/', [QuizController::class, 'index'])->name('quiz.index');
    Route::get('get-question', [QuizController::class, 'getQuestion'])->name('quiz.question');
});

