<?php

namespace App\Services\Membership;

use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use App\Models\IosMembership;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IAPMembershipService
{
  protected $issuerId;
  protected $keyId;
  protected $privateKeyPath;
  protected $baseUrl;
  protected $bundleId;

  public function __construct()
  {
    $this->baseUrl = config('google-play.api.apple_url');
    $this->issuerId = config('google-play.apple_issuer_id');
    $this->keyId = config('google-play.apple_key_id');
    $this->privateKeyPath = storage_path('app/private/SubscriptionKey_KYZT3B6GHH.p8');
    $this->bundleId = config('google-play.bundle_id');
  }

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

  // $token = "eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6IktZWlQzQjZHSEgifQ.eyJpc3MiOiI1OTY1NzA5NS0wODFlLTQzZTEtYTZkZi0yNTQ5MWRlNDAwNDIiLCJpYXQiOjE3NjI1OTczMjcsImV4cCI6MTc2MjU5NzYyNywiYXVkIjoiYXBwc3RvcmVjb25uZWN0LXYxIiwiYmlkIjoiY29tLmR0dC1jYXItYmlrZS1pcmVsYW5kIn0.sudm7rvqCxLg7RTzEFMFY4CW855TL3UjBOx5nqiP0DVddqJmASf6-5iyIn6HzlL4evy7QtbMplFyoWfue5uDYQ";
  public function verifySubscription(string $receipt)
  {
    try {
      $token = $this->generateJwt();
      $endpoint = "inApps/v1/subscriptions/{$receipt}";

      $client = new Client([
        'base_uri' => $this->baseUrl,
        'headers' => [
          'Authorization' => "Bearer {$token}",
          'Accept' => 'application/json',
        ],
        'verify' => app()->environment('production'), // true in prod, false in sandbox
        'timeout' => 10,
      ]);

      $response = $client->get($endpoint);
      $status = $response->getStatusCode();

      if ($status !== 200) {
        return ['error' => "Apple API responded with status {$status}"];
      }

      $data = json_decode($response->getBody()->getContents(), true);

      $lastTransaction = $data['data'][0]['lastTransactions'][0] ?? null;
      if (!$lastTransaction) {
        return ['message' => 'No transaction data found', 'response' => $data];
      }

      $transaction = $this->decodeJws($lastTransaction['signedTransactionInfo'] ?? null);
      $renewal = $this->decodeJws($lastTransaction['signedRenewalInfo'] ?? null);

      return [
        'transaction' => $transaction,
        'renewal' => $renewal,
        'raw' => $data,
      ];
    } catch (\GuzzleHttp\Exception\ClientException $e) {
      // Handle Apple 401/404, etc.
      return [
        'error' => 'Apple API error',
        'response' => json_decode($e->getResponse()?->getBody()?->getContents() ?? '{}', true),
      ];
    } catch (Exception $e) {
      return [
        'error' => $e->getMessage(),
        'trace' => config('app.debug') ? $e->getTraceAsString() : null,
      ];
    }
  }

  private function decodeJws(?string $jws): ?array
  {
    if (!$jws || !str_contains($jws, '.')) {
      return null;
    }

    [, $payload] = explode('.', $jws, 3);
    $decoded = base64_decode(strtr($payload, '-_', '+/'));
    return json_decode($decoded, true);
  }

  protected function generateJwt()
  {
    $privateKey = file_get_contents($this->privateKeyPath);
    $now = time();

    $payload = [
      'iss' => $this->issuerId,
      'iat' => $now,
      'exp' => $now + 300, // valid for 5 minutes
      'aud' => 'appstoreconnect-v1',
      'bid' => $this->bundleId,
    ];

    $header = [
      'alg' => 'ES256',
      'kid' => $this->keyId,
      'typ' => 'JWT'
    ];

    $jwt = JWT::encode(
      $payload,
      $privateKey,
      'ES256',
      $this->keyId,  // This sets the 'kid' in header
      $header
    );
    return $jwt;
  }
}