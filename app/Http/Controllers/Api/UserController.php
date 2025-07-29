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

        if ($existingDevice) {
            return response()->json([
                'error' => 'Device already registered',
            ], 409);
        }

        // Create new user
        $user = User::create([
            'device_id' => $deviceId // Dummy password for device-only users
        ]);

        $role = Role::where(['name' => 'student'])->first();
        $user->assignRole($role);

        // Create free membership automatically
        $this->membershipService->createFreeMembership($user);

        return response()->json([
            'success' => 'Device registered successfully',
            'data' => $user,
        ], 201);
    }
}
