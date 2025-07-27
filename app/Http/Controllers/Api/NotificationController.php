<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $deviceId = $request->header('Device-Id');

        $user = User::where('device_id', $deviceId)->first();

        $notifications = $user->notifications()->paginate(20);

        return response()->json([
            'status' => true,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $deviceId = $request->header('Device-Id');

        $user = User::where('device_id', $deviceId)->first();

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json(['status' => false, 'message' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['status' => true, 'message' => 'Notification marked as read.']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $deviceId = $request->header('Device-Id');

        $user = User::where('device_id', $deviceId)->first();

        $user->unreadNotifications->markAsRead();

        return response()->json(['status' => true, 'message' => 'All notifications marked as read.']);
    }
}
