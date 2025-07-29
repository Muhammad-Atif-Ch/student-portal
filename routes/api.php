<?php

use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PrivacyPolicyController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::withoutMiddleware(['device.check'])->group(function () {
    Route::get('privacy-policy', [PrivacyPolicyController::class, 'index'])->name('privacy-policy');
    Route::get('user/device-register', [UserController::class, 'register']);
    Route::post('contact-us', [ContactUsController::class, 'index']);
    Route::get('app-image', [SettingController::class, 'appImage']);
});

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
    Route::get('previous-test-result-report', [ResultController::class, 'previousTestResultReport']);
    Route::get('previous-test-result-details', [ResultController::class, 'previousTestResultDetails']);
    Route::get('result-summary', [ResultController::class, 'resultSummary']);
    Route::get('result-category', [ResultController::class, 'resultCategory']);

    // Contact Us related route
    // Route::get('contact-us', [ContactUsController::class, 'index']);
});

Route::get('/languages', [SettingController::class, 'languages']);

Route::group(['prefix' => 'setting'], function () {
    // Result related routes
    Route::get('/', [SettingController::class, 'index']);
    Route::post('update', [SettingController::class, 'update']);
});

Route::group(['prefix' => 'notification'], function () {
    // Result related routes
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
});

// // Membership management routes
Route::prefix('membership')->group(function () {
    Route::get('/', [MembershipController::class, 'index']);
    Route::post('/store', [MembershipController::class, 'store']);
});

