<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index(Request $request)
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

        return response()->json([
            'success' => 'Device registered successfully',
            'data' => $user,
        ], 201);
    }
}
