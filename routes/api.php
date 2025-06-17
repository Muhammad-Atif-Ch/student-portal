<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\PrivacyPolicyController;

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
    Route::get('previous-incorrect', [QuizController::class, 'previousIncorrect']);
    Route::get('least-seen', [QuizController::class, 'leastSeen']);

    // Question related routes
    Route::get('get-flag', [QuestionController::class, 'getFlag']);
    Route::post('store-flag', [QuestionController::class, 'storeFlag']);

    // Result related routes
    Route::get('previous-test-result', [ResultController::class, 'previousTestResult']);
    Route::get('previous-test-result-details', [ResultController::class, 'previousTestResultDetails']);
    Route::get('result-summary', [ResultController::class, 'resultSummary']);
    Route::get('result-category', [ResultController::class, 'resultCategory']);
});

Route::group(['prefix' => 'setting'], function () {
    // Result related routes
    Route::get('/', [SettingController::class, 'index']);
    Route::post('update', [SettingController::class, 'update']);
});

Route::get('privacy-policy', [PrivacyPolicyController::class, 'index'])->name('privacy-policy');

