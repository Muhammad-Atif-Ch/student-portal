<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Membership\MembershipService;
use Symfony\Component\HttpFoundation\Response;

class CheckMembership
{
    protected MembershipService $membershipService;

    public function __construct(MembershipService $membershipService)
    {
        $this->membershipService = $membershipService;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deviceId = $request->header('Device-ID');

        if (!$deviceId) {
            return response()->json(['error' => 'Device ID required'], 400);
        }

        // Find user
        $user = User::with('membership')->where('device_id', $deviceId)->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'message' => 'Invalid user provided'
            ], 404);
        }

        // Check membership access
        $accessInfo = $user->membership;
dd($accessInfo, $accessInfo->status == false);
        if ($accessInfo->status == false) {
            return response()->json([
                'error' => 'Access denied',
                'message' => "your membership expired. Please renew it.",
                'membership_required' => true,
                'membership_type' => $accessInfo->membership_type,
                'end_date' => $accessInfo->end_date
            ], 403);
        }

        return $next($request);
    }
}
