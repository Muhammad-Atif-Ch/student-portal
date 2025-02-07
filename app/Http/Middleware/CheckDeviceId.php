<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDeviceId
{
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
        
        if (!User::where('device_id', $deviceId)->exists()) {
            return response()->json(['error' => 'Unauthorized device'], 403);
        }

        return $next($request);
    }
}
