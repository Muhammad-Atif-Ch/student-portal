<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Responses\UserResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Membership\MembershipService;
use App\Services\Membership\IAPMembershipService;
use App\Http\Requests\Api\Membership\CreateMembershipRequest;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $user = User::with('membership')->where('device_id', $deviceId)->first();

        return response()->json([
            'success' => 'data',
            'data' => $user,
        ], 200);
    }

    public function create()
    {
        // Logic to show form for creating a new membership plan
    }

    public function store(CreateMembershipRequest $request)
    {
        $deviceId = $request->header('Device-Id'); //shared_secret
        $user = User::where('device_id', $deviceId)->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->platform = $request->platform;
        $user->purchase_token = $request->purchase_token;
        $user->save();

        $user->refresh();

        // dd($user->membership->toArray());
        if ($user->platform === 'ios') {
            $purchaseToken = $user->purchase_token; // Adjust if you store purchaseToken separately

            $data = (new IAPMembershipService)->verifySubscription($request->purchase_token);
            // dd($data);

            if ($data) {
                $subscription = [
                    'user_id' => $user->id,
                    'membership_type' => 'premium',
                    'product_id' => $data['transaction']['productId'],
                    'transaction_id' => $data['transaction']['transactionId'],
                    'original_transaction_id' => $data['transaction']['originalTransactionId'],
                    'environment' => $data['transaction']['environment'],
                    'purchase_date' => $data['transaction']['purchaseDate'],
                    'expires_date' => $data['transaction']['expiresDate'],
                    'price' => $data['transaction']['price'],
                    'currency' => $data['transaction']['currency'],
                    'subscription_group_identifier' => $data['transaction']['subscriptionGroupIdentifier'] ?? null,
                    'auto_renew_status' => $data['renewal']['autoRenewStatus'] ?? null,
                    'auto_renew_product_id' => $data['renewal']['autoRenewProductId'] ?? null,
                    'receipt_data' => $data['transaction'],
                    'raw_response' => json_encode($data['raw']),
                    'status' => $data['raw']['data'][0]['lastTransactions'][0]['status'],
                ];

                $env = $data['environment'] ?? 'Unknown';
                // Update membership with new data
                $user->iosMembership()->updateOrCreate([
                    'user_id' => $user->id,
                ], $subscription);

                $message = "Membership updated successfully (Environment: {$env}).";
                $user->refresh();
                $user->load('iosMembership');
            } else {
                return response()->json(['error' => $data], 400);
            }
        } else if ($user->platform === 'android') {
            $packageName = config('google-play.package_name'); // Store in config/google-play.php
            $subscriptionId = config('google-play.subscription_id'); // Store in config/google-play.php
            $purchaseToken = $user->purchase_token; // Adjust if you store purchaseToken separately

            $data = (new MembershipService)->verifySubscription($packageName, $subscriptionId, $purchaseToken);

            // dd($data);
            if ($data) {
                // Check if this is fallback data
                $isFallback = isset($data['is_fallback']) && $data['is_fallback'];

                // Update membership with new data
                $user->membership()->updateOrCreate([
                    'user_id' => $user->id,
                ], [
                    'membership_type' => 'premium',
                    'start_date' => Carbon::createFromTimestampMs($data['startTimeMillis'] ?? null)->setTimezone('UTC'),
                    'end_date' => Carbon::createFromTimestampMs($data['expiryTimeMillis'] ?? null)->setTimezone('UTC'),
                    'auto_renewing' => $data['autoRenewing'] ?? false,
                    'order_id' => $data['orderId'] ?? null,
                    'price_currency_code' => $data['priceCurrencyCode'] ?? null,
                    'price_amount_micros' => ($data['priceAmountMicros'] / 1000000) ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'cancel_reason' => $data['cancelReason'] ?? null,
                    'purchase_type' => $data['purchaseType'] ?? null,
                    'acknowledgement_state' => $data['acknowledgementState'] ?? null,
                    'raw_response' => $data,
                    'status' => isset($data['expiryTimeMillis']) && $data['expiryTimeMillis'] > now()->getTimestampMs(),
                ]);

                $message = $isFallback
                    ? "Membership created with fallback data due to network issues. Please try again later."
                    : "Membership for user {$user->id} updated.";
                $user->refresh();
                $user->load('membership');
            }
        } else {
            return response()->json(['error' => "Failed to verify subscription for user {$user->id}. Please check your internet connection and try again."], 400);
        }


        return response()->json(['success' => $message, 'data' => $user], 200);
    }
}
