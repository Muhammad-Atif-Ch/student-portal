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
    public function handle(Request $request, Closure $next, $type): Response
    {
        $deviceId = $request->header('Device-ID');

        if (!$deviceId) {
            return response()->json(['error' => 'Device ID required'], 400);
        }

        // Find user
        $user = User::where('device_id', $deviceId)->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'message' => 'Invalid user provided'
            ], 404);
        }
        $accessInfo = $user->active_membership;

        // if ($accessInfo->status == 0) {
        //     return response()->json([
        //         'error' => 'Access denied',
        //         'message' => "your membership expired. Please renew it.",
        //         'membership_required' => true,
        //         'membership_type' => $accessInfo->membership_type,
        //         'end_date' => $accessInfo->end_date
        //     ], 403);
        // }

        if ($accessInfo->status == 0) {
            // ✅ Only FREE routes allowed
            if ($type === 'free') {
                return $next($request);
            }

            // ❌ Everything else blocked
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Your membership has expired. Only free features are available.',
                'membership_required' => true,
                'membership_type' => $accessInfo->membership_type,
                'end_date' => $accessInfo->end_date,
            ], Response::HTTP_FORBIDDEN);
        }

        // Handle different access types
        switch ($type) {
            case 'premium':
                if ($accessInfo->membership_type !== 'premium') {
                    return response()->json(['message' => 'Upgrade to premium to access this feature.'], Response::HTTP_FORBIDDEN);
                }
                break;

            case 'free':
                // Allow both free and premium users
                if (!in_array($accessInfo->membership_type, ['free', 'premium'])) {
                    return response()->json(['message' => 'Access denied.'], Response::HTTP_FORBIDDEN);
                }
                break;
        }

        return $next($request);
    }
}
