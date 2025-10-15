<?php

namespace App\Services\Membership;

use Exception;
use App\Models\Membership;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IAPMembershipService
{
  public function createFreeMembership($user)
  {
    return Membership::updateOrCreate(
      ['user_id' => $user->id],
      [
        'user_id' => $user->id,
        'product_id' => null,
        'transaction_id' => null,
        'original_transaction_id' => null,
        'purchase_date' => null,
        'expires_date' => null,
        'is_trial_period' => null,
        'is_in_intro_offer_period' => null,
        'subscription_group_identifier' => null,
        'auto_renew_status' => null,
        'auto_renew_product_id' => null,
        'environment' => null,
        'receipt_data' => null,
        'raw_response' => null,
        'status' => null,
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