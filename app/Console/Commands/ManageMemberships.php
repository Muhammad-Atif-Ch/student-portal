<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\ToArray;
use App\Services\Membership\MembershipService;

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
        $now = Carbon::now();
        // Free memberships to deactivate
        $freeUsers = User::whereHas('membership', function ($query) use ($now) {
            $query->where('membership_type', 'free')
                ->where('status', 1)
                ->where('end_date', '<', $now->subDay()); // end_date + 1 day < now
        })->with('membership')->get();

        foreach ($freeUsers as $user) {
            $user->membership->update(['status' => 0]);
            $this->info("Free membership for user {$user->id} deactivated (expired + grace period).");
        }

        // Premium memberships to check with Google API
        $premiumUsers = User::whereHas('membership', function ($query) use ($now) {
            $query->where('membership_type', 'premium')
                ->where('status', 1)
                ->where('end_date', '<', $now);
        })->with('membership')->get();

        $this->info("Found {$premiumUsers->count()} users with expired premium memberships");

        foreach ($premiumUsers as $user) {
            // dd($user->membership->toArray());
            $packageName = config('google-play.package_name'); // Store in config/google-play.php
            $subscriptionId = config('google-play.subscription_id'); // Store in config/google-play.php
            $purchaseToken = $user->purchase_token; // Adjust if you store purchaseToken separately

            $data = (new MembershipService)->verifySubscription($packageName, $subscriptionId, $purchaseToken);
            //dd('data', $data, 'nextt ', isset($data['expiryTimeMillis']) && $data['expiryTimeMillis'] > now()->getTimestampMs());

            if ($data) {
                // Update membership with new data
                $user->membership->update([
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

                $this->info("Membership for user {$user->id} updated.");
            } else {
                $this->error("Failed to verify subscription for user {$user->id}: " . $data->body());
            }
        }
    }
}
