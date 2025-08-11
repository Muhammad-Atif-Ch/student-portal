<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Services\Membership\MembershipService;

class UserController extends Controller
{
    protected MembershipService $membershipService;

    public function __construct(MembershipService $membershipService)
    {
        $this->membershipService = $membershipService;
    }

    public function register(Request $request)
    {
        $deviceId = $request->header('Device-ID');

        // Check if device exists
        $existingDevice = User::where('device_id', $deviceId)->first();
        if ($existingDevice && $request->filled('fcm_token')) {
            $existingDevice->update([
                "fcm_token" => $request->fcm_token,
            ]);
        }

        if ($existingDevice) {
            return response()->json([
                'error' => 'Device already registered',
            ], 409);
        }

        // Create new user
        $user = User::create([
            'device_id' => $deviceId, // Dummy password for device-only users
            'language_id' => 41,
            'app_type' => 'car',
            "fcm_token" => $request->fcm_token,
        ]);

        $role = Role::where(['name' => 'student'])->first();
        $user->assignRole($role);

        // Create free membership automatically
        $this->membershipService->createFreeMembership($user);

        $user->refresh()->load('membership');

        return response()->json([
            'success' => 'Device registered successfully',
            'data' => $user,
        ], 201);
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
