<?php

namespace App\Services\Membership;

use App\Models\IosMembership;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IAPMembershipService
{
  public function createFreeMembership($user)
  {
    return IosMembership::updateOrCreate(
      ['user_id' => $user->id],
      [
        'user_id' => $user->id,
        'membership_type' => 'free',
        'purchase_date' => now(),
        'expires_date' => now()->addDay(),
        'status' => 1,
      ]
    );
  }

  public function verifySubscription(string $receipt)
  {
    $iapUrlProduction = config('google-play.api.iap_url_production');
    $iapUrlSandbox = config('google-play.api.iap_url_sandbox');
    $sharedSecret = config('google-play.shared_secret');

    try {
      // Send verification request to Apple production
      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
      ])->withBody(json_encode([
              'receipt-data' => $receipt,
              'password' => $sharedSecret,
              'exclude-old-transactions' => true,
            ]), 'application/json')->post($iapUrlProduction);

      $data = $response->json();

      // If the receipt is from sandbox, Apple returns status 21007
      if (isset($data['status']) && $data['status'] == 21007) {
        $response = Http::withHeaders([
          'Content-Type' => 'application/json',
        ])->withBody(json_encode([
                'receipt-data' => $receipt,
                'password' => $sharedSecret,
                'exclude-old-transactions' => true,
              ]), 'application/json')->post($iapUrlSandbox);

        $data = $response->json();
        $data['is_fallback'] = true; // mark that we used sandbox fallback
      } else {
        $data['is_fallback'] = false;
      }

      return $data;
    } catch (\Throwable $e) {
      // Log the error for debugging
      Log::error("Failed to verify subscription: " . $e->getMessage());
      return $e->getMessage();
    }
  }
}