<?php

namespace App\Services\Membership;

use Carbon\Carbon;
use Google\Client;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class MembershipService
{
  public function createFreeMembership($user)
  {
    return Membership::updateOrCreate(
      ['user_id' => $user->id],
      [
        'membership_type' => 'free',
        'start_date' => now(),
        'end_date' => now()->addDay(),
        'status' => true,
        // Add other fields as needed, set to null or default for free
        'auto_renewing' => false,
        'order_id' => null,
        'price_currency_code' => null,
        'price_amount_micros' => null,
        'country_code' => null,
        'cancel_reason' => null,
        'purchase_type' => null,
        'acknowledgement_state' => null,
        'raw_response' => null,
      ]
    );
  }

  public function verifySubscription(string $packageName, string $subscriptionId, string $purchaseToken)
  {
    $baseUrl = config('google-play.api.base_url');
    $accessToken = $this->getGoogleAccessToken(); // Your existing method
    $url = "{$baseUrl}/applications/{$packageName}/purchases/subscriptions/{$subscriptionId}/tokens/{$purchaseToken}";
    // dd($purchaseToken, $accessToken, $url);

    try {
      $response = Http::timeout(30)
        ->retry(3, 1000) // Retry 3 times with 1 second delay
        ->withToken($accessToken)
        ->get($url);
        
      // dd($response);
      if ($response->successful()) {
        return $response->json();
      } else {
        throw new \Exception("Google API error: " . $response->body());
      }

    } catch (\Throwable $e) {
      // Log the error for debugging
      Log::error("Failed to verify subscription: " . $e->getMessage());

      // If it's a network connectivity issue, create a fallback membership
      if (
        str_contains($e->getMessage(), 'cURL error 28') ||
        str_contains($e->getMessage(), 'Failed to connect') ||
        str_contains($e->getMessage(), 'timeout')
      ) {

        Log::warning("Network connectivity issue detected. Creating fallback membership.");
        return $this->createFallbackMembershipData();
      }

      return null;
    }
  }

  /**
   * Create fallback membership data when Google API is unavailable
   */
  private function createFallbackMembershipData()
  {
    return [
      'startTimeMillis' => now()->getTimestampMs(),
      'expiryTimeMillis' => now()->addDays(1)->getTimestampMs(),
      'autoRenewing' => false,
      'orderId' => 'fallback_' . time(),
      'priceCurrencyCode' => null,
      'priceAmountMicros' => 0,
      'countryCode' => null,
      'cancelReason' => null,
      'purchaseType' => 0,
      'acknowledgementState' => 1,
      'is_fallback' => true,
    ];
  }


  public function getGoogleAccessToken()
  {
    $client = new Client();
    $client->setAuthConfig(storage_path('app/private/google-service-account.json'));
    $client->addScope('https://www.googleapis.com/auth/androidpublisher');
    $client->setSubject(null); // Usually not needed for service accounts

    // Configure timeouts and retries for better network handling
    $client->setHttpClient(new \GuzzleHttp\Client([
      'timeout' => 30,
      'connect_timeout' => 10,
      'retry_on_status' => [408, 429, 500, 502, 503, 504],
      'max_retry_attempts' => 3,
    ]));

    // Fetch the access token
    $tokenArray = $client->fetchAccessTokenWithAssertion();
    return $tokenArray['access_token'];
  }
}