<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Membership\CreateMembershipRequest;
use App\Responses\UserResponse;
use App\Services\Membership\MembershipService;

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
        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();

        $user->purchase_token = $request->purchase_token;
        $user->save();

        $user->refresh();

        // dd($user->membership->toArray());
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
                'start_date' => Carbon::createFromTimestampMs($data['startTimeMillis'] ?? null),
                'end_date' => Carbon::createFromTimestampMs($data['expiryTimeMillis'] ?? null),
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

            return response()->json(['success' => $message], 200);
        } else {
            return response()->json(['error' => "Failed to verify subscription for user {$user->id}. Please check your internet connection and try again."], 400);
        }
    }

    public function edit($id)
    {
        // Logic to show form for editing an existing membership plan
    }

    public function update(Request $request, $id)
    {
        // Logic to update an existing membership plan
    }

    public function destroy($id)
    {
        // Logic to delete a membership plan
    }
}
