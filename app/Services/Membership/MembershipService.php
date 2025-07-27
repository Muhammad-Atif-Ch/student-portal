<?php

namespace App\Services\Membership;

use Carbon\Carbon;
use Google\Client;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Support\Facades\Http;
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
    // dd($packageName, $subscriptionId, $purchaseToken, $accessToken, $url);

    try {
        $response = Http::withToken($accessToken)->get($url);
// dd($response);
        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception("Google API error: " . $response->body());
        }

    } catch (\Throwable $e) {
        // Log or rethrow as needed
        \Log::error("Failed to verify subscription: " . $e->getMessage());
        return null;
    }
}


  public function getGoogleAccessToken()
  {
    $client = new Client();
    $client->setAuthConfig(storage_path('app/private/google-service-account.json'));
    $client->addScope('https://www.googleapis.com/auth/androidpublisher');
    $client->setSubject(null); // Usually not needed for service accounts
    

    // Fetch the access token
    $tokenArray = $client->fetchAccessTokenWithAssertion();
    return $tokenArray['access_token'];
  }
}