<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MembershipService;
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
        // Get user ID from request
        $userId = $request->input('user_id') ?? $request->user_id ?? $request->header('X-User-ID');

        if (!$userId) {
            return response()->json([
                'error' => 'User ID required',
                'message' => 'Please provide user_id in request'
            ], 400);
        }

        // Find user
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'message' => 'Invalid user ID provided'
            ], 404);
        }

        // Check membership access
        $accessInfo = $this->membershipService->canUserAccessApp($user);

        if (!$accessInfo['can_access']) {
            return response()->json([
                'error' => 'Access denied',
                'message' => $accessInfo['message'],
                'membership_required' => true,
                'membership_type' => $accessInfo['membership_type'],
                'end_date' => $accessInfo['end_date']
            ], 403);
        }

        // Add user and membership info to request
        $request->merge([
            'user_instance' => $user,
            'membership_info' => $accessInfo
        ]);

        return $next($request);
    }
}
