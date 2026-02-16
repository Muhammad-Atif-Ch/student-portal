<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Membership;
use Illuminate\Http\Request;
use App\Models\IosMembership;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Services\CurrencyRateService\CurrencyRateService;

class DashboardController extends Controller
{
    public function index()
    {
        $membership = User::query()
            ->leftJoin('memberships', function ($join) {
                $join->on('users.id', '=', 'memberships.user_id');
            })
            ->leftJoin('ios_memberships', function ($join) {
                $join->on('users.id', '=', 'ios_memberships.user_id');
            })
            ->selectRaw("
            COUNT(CASE WHEN users.platform = 'android' AND memberships.membership_type = 'free' THEN 1 END) as android_free,
            COUNT(CASE WHEN users.platform = 'android' AND memberships.membership_type = 'premium' THEN 1 END) as android_premium,
            COUNT(CASE WHEN users.platform = 'ios' AND ios_memberships.membership_type = 'free' THEN 1 END) as ios_free,
            COUNT(CASE WHEN users.platform = 'ios' AND ios_memberships.membership_type = 'premium' THEN 1 END) as ios_premium
        ")
            ->first();

        // $languageUsage = User::query()
        //     ->with('language')
        //     ->select('language_id', DB::raw('count(*) as total'))
        //     ->whereNotNull('language_id')
        //     ->groupBy('language_id')
        //     ->get();

        $ios = IosMembership::query()
            ->where('membership_type', 'premium')
            ->selectRaw('SUM(price * (SELECT rate_to_usd FROM currency_rates WHERE currency_rates.currency = ios_memberships.currency)) as total_usd')
            ->value('total_usd');

        $android = Membership::query()
            ->where('membership_type', 'premium')
            ->selectRaw('SUM(price * (SELECT rate_to_usd FROM currency_rates WHERE currency_rates.currency = memberships.currency)) as total_usd')
            ->value('total_usd');
        $revenue = $ios + $android;
        // dd($ios, $android, $revenue);

        // $priceUsd = $this->service->convertToUSD($price, $currency);

        $user = User::get();
        return view("backend.dashboard.index", compact('membership', 'revenue'));
    }

    public function filterLanguageUsage(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $languageUsage = User::query()
            ->with('language')
            ->select('language_id', DB::raw('count(*) as total'))
            ->whereNotNull('language_id')
            ->whereBetween('updated_at', [$request->start_date, $request->end_date])
            ->groupBy('language_id')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json(['data' => $languageUsage, 'success' => true]);
    }
}
