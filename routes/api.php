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
    Route::get('/', [QuizController::class, 'index']);
    Route::get('search-question', [QuizController::class, 'searchQuestion']);
    Route::get('get-read-question', [QuizController::class, 'getReadQuestion']);
    Route::get('get-practice-question', [QuizController::class, 'getPracticeQuestion']);
    Route::get('get-official-question', [QuizController::class, 'getOfficialQuestion']);
    Route::post('store', [QuizController::class, 'store']);
});

