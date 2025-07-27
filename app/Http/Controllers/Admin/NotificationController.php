<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FcmNotification\FcmNotificationService;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{

    protected FcmNotificationService $fcmNotificationService;

    public function __construct(FcmNotificationService $fcmNotificationService)
    {
        $this->fcmNotificationService = $fcmNotificationService;
    }

    public function index()
    {
        // Fetch notifications from the database
        $notifications = Notification::all();

        // Return the view with notifications data
        return view('backend.notification.index', compact('notifications'));
    }

    public function create()
    {
        // Return the view to create a new notification
        return view('backend.notification.create');
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Create a new notification
        Notification::create([
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        $tokens = User::pluck('fcm_token');

        foreach ($tokens as $token) {
            $this->fcmNotificationService->sendFcmNotification(
                $token,
                $request->subject,
                $request->message,
            );
        }

        // Redirect back with success message
        return redirect()->route('admin.notification.index')->with('success', 'Notification created successfully.');
    }

    
}
