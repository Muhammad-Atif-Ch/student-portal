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

Route::withoutMiddleware(['device.check', 'membership'])->group(function () {
    Route::get('privacy-policy', [PrivacyPolicyController::class, 'index'])->name('privacy-policy');
    Route::post('user/device-register', [UserController::class, 'register']);
    Route::post('contact-us', [ContactUsController::class, 'index']);
    Route::get('app-image', [SettingController::class, 'appImage']);
    Route::get('user-status', [UserController::class, 'userStatus']);
    Route::get('languages', [SettingController::class, 'languages']);

    // Membership management routes
    Route::prefix('membership')->group(function () {
        Route::get('/', [MembershipController::class, 'index']);
        Route::post('/store', [MembershipController::class, 'store']);
    });
});

// Routes only for premium users
Route::middleware('membership:premium')->group(function () {
    Route::group(['prefix' => 'quiz'], function () {
        // Route::get('get-read-question', [QuizController::class, 'getReadQuestion']);
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

    Route::group(['prefix' => 'notification'], function () {
        // Result related routes
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

});

// Route only for free users
Route::middleware('membership:free')->group(function () {
    Route::group(['prefix' => 'quiz'], function () {
        Route::get('/', [QuizController::class, 'index']);
        Route::get('search-question', [QuizController::class, 'searchQuestion']);
        Route::get('get-read-question', [QuizController::class, 'getReadQuestion']);
    });

    // setting routes
    Route::group(['prefix' => 'setting'], function () {
        // Result related routes
        Route::get('/', [SettingController::class, 'index']);
        Route::post('update', [SettingController::class, 'update']);
    });


});

// In routes/web.php or api.php - TEMPORARY DEBUG ROUTE
Route::get('/test-apple-jwt', function () {
    $privateKeyPath = storage_path('app/private/SubscriptionKey_KYZT3B6GHH.p8');
    $privateKey = file_get_contents($privateKeyPath);

    $issuerId = '59657095-081e-43e1-a6df-25491de40042';
    $keyId = 'KYZT3B6GHH';
    $bundleId = 'com.dtt-car-bike-ireland';

    $now = time();

    $payload = [
        'iss' => $issuerId,
        'iat' => $now,
        'exp' => $now + 300,
        'aud' => 'appstoreconnect-v1',
        'bid' => $bundleId,
    ];

    try {
        $jwt = \Firebase\JWT\JWT::encode(
            $payload,
            $privateKey,
            'ES256',
            $keyId
        );

        // Decode the token to inspect it
        $parts = explode('.', $jwt);
        $header = json_decode(base64_decode($parts[0]), true);
        $decodedPayload = json_decode(base64_decode($parts[1]), true);

        return response()->json([
            'success' => true,
            'jwt' => $jwt,
            'header' => $header,
            'payload' => $decodedPayload,
            'private_key_info' => [
                'exists' => file_exists($privateKeyPath),
                'starts_with' => substr($privateKey, 0, 27),
                'length' => strlen($privateKey),
            ]
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

