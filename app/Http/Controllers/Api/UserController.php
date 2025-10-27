<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Membership\MembershipService;
use App\Services\Membership\IAPMembershipService;

class UserController extends Controller
{
    public function __construct(protected MembershipService $membershipService, protected IAPMembershipService $iapMembershipService)
    {
        $this->membershipService = $membershipService;
        $this->iapMembershipService = $iapMembershipService;
    }

    public function register(Request $request)
    {
        // Validate input
        $request->validate([
            'platform' => 'required|in:ios,android',
            'fcm_token' => 'nullable|string',
        ]);

        // Ensure Device-ID header exists
        $deviceId = $request->header('Device-ID');
        if (!$deviceId) {
            return response()->json([
                'error' => 'Missing header: Device-ID'
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $deviceId) {

                // Check if user already registered with this device
                $existingUser = User::where('device_id', $deviceId)->first();

                // If user exists, update FCM token if provided
                if ($existingUser) {
                    if ($request->filled('fcm_token')) {
                        $existingUser->update(['fcm_token' => $request->fcm_token]);
                    }

                    if ($request->platform === 'ios') {
                        $existingUser->load('iosmembership');
                    } else {
                        $existingUser->load('membership');
                    }

                    return response()->json([
                        'error' => 'Device already registered',
                        'data' => $existingUser
                    ], 409);
                }

                // Create new user
                $user = User::create([
                    'device_id' => $deviceId,
                    'language_id' => 41,
                    'app_type' => 'car',
                    'fcm_token' => $request->fcm_token,
                    'platform' => $request->platform,
                ]);

                // Assign "student" role
                $role = Role::where('name', 'student')->first();
                if ($role) {
                    $user->assignRole($role);
                }

                // Automatically create free membership based on platform
                if ($request->platform === 'ios') {
                    $this->iapMembershipService->createFreeMembership($user);
                    $user->load('iosmembership');
                } else {
                    $this->membershipService->createFreeMembership($user);
                    $user->load('membership');
                }

                return response()->json([
                    'success' => 'Device registered successfully',
                    'data' => $user,
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Register failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function userStatus(Request $request)
    {
        if (!$request->hasHeader('Device-ID')) {
            return response()->json(['message' => 'Missing header: Device-ID'], 422);
        }

        $deviceId = $request->header('Device-ID');

        // Check if device exists
        $user = User::with('membership')->where('device_id', $deviceId)->first();
        // dd($user);
        if ($user == null) {
            return response()->json([
                'error' => 'Device not found',
            ], 404);
        }

        return response()->json([
            'success' => 'Success',
            'data' => $user,
        ], 200);
    }
}
