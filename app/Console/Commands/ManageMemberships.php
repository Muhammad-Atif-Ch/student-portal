<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;
use App\Services\Membership\MembershipService;
use App\Services\Membership\IAPMembershipService;

class ManageMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memberships:manage';

    protected $description = 'Verify Google Play subscriptions and update memberships';

    public function handle()
    {
        try {
            $now = Carbon::now();

            // 1️⃣ Fetch all users (iOS + Android) in one query
            $users = User::where(function ($query) use ($now) {
                $query->whereHas('membership', function ($q) use ($now) {
                    $q->where('status', 1)
                        ->where('end_date', '<', $now);
                })->orWhereHas('iosMembership', function ($q) use ($now) {
                    $q->where('status', 1)
                        ->where('expires_date', '<', $now);
                });
            })
                ->with(['membership', 'iosMembership'])
                ->get();

            // 2️⃣ Create a unified reference for easier filtering
            $users->transform(function ($user) {
                $user->current_membership = $user->platform === 'ios'
                    ? $user->iosMembership
                    : $user->membership;
                return $user;
            });

            // 3️⃣ Filter in-memory
            $freeUsers = $users->filter(fn($u) => $u->current_membership && $u->current_membership->membership_type === 'free');
            $premiumUsers = $users->filter(fn($u) => $u->current_membership && $u->current_membership->membership_type === 'premium');

            // dd("free user", $freeUsers->toArray(), "premium user ", $premiumUsers[0]->platform);

            foreach ($freeUsers as $user) {
                $user->current_membership->update(['status' => 0]);
                $this->info("Free membership for user {$user->id} deactivated (expired + grace period).");
            }

            $this->info("Found {$premiumUsers->count()} users with expired premium memberships");

            foreach ($premiumUsers as $user) {
                $packageName = config('google-play.package_name'); // Store in config/google-play.php
                $subscriptionId = config('google-play.subscription_id'); // Store in config/google-play.php
                $purchaseToken = $user->purchase_token ?? null;

                if ($user->platform === 'android' && $purchaseToken) {
                    $data = (new MembershipService)->verifySubscription($packageName, $subscriptionId, $purchaseToken);

                    if ($data['success'] == true) {
                        $data = $data['data'];
                        // Update membership with new data
                        $user->current_membership->update([
                            'start_date' => Carbon::createFromTimestampMs($data['startTimeMillis'] ?? null),
                            'end_date' => Carbon::createFromTimestampMs($data['expiryTimeMillis'] ?? null),
                            'auto_renewing' => $data['autoRenewing'] ?? false,
                            'order_id' => $data['orderId'] ?? null,
                            'price' => ($data['priceAmountMicros'] / 1000000) ?? null,
                            'currency' => $data['priceCurrencyCode'] ?? null,
                            'country_code' => $data['countryCode'] ?? null,
                            'cancel_reason' => $data['cancelReason'] ?? null,
                            'purchase_type' => $data['purchaseType'] ?? null,
                            'acknowledgement_state' => $data['acknowledgementState'] ?? null,
                            'raw_response' => $data,
                            'status' => isset($data['expiryTimeMillis']) && $data['expiryTimeMillis'] > now()->getTimestampMs(),
                        ]);

                        $this->info("Membership for user {$user->id} updated.");
                    } else {
                        $this->error("Failed to verify subscription for user {$user->id}: " . $data['message']);
                    }
                } else if ($user->platform === 'ios' && $purchaseToken) {
                    $data = (new IAPMembershipService)->verifySubscription($purchaseToken);

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
                    if ($data) {
                        // Update membership with new data
                        $user->current_membership->updateOrCreate([
                            'user_id' => $user->id,
                        ], $subscription);
                        $this->info("Membership for user {$user->id} updated.");
                    } else {
                        $this->error("Failed to verify subscription for user {$user->id}");
                    }
                } else {
                    $this->error("User platform not found. {$user->id}");
                }
            }
        } catch (\Throwable $e) {
            $this->error("Error managing memberships: " . $e->getMessage());
        }
    }
}
