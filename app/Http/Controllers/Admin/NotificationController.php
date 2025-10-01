<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Notifications\CustomNotification;
use App\Services\FcmNotification\FcmNotificationService;

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
        $notifications = Notification::select(
            'data->subject as subject',
            'data->message as message'
        )->distinct()->get();

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
        // Notification::create([
        //     'subject' => $request->subject,
        //     'message' => $request->message,
        // ]);
        $data = [
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        $users = User::whereNotNull('fcm_token')->get();
        if ($users->isEmpty()) {
            return redirect()->route('admin.notification.index')->with('error', 'User not found.');
        }

        foreach ($users as $user) {
            $user->notify(new CustomNotification($data));
            $this->fcmNotificationService->sendFcmNotification(
                $user->fcm_token,
                $request->subject,
                $request->message,
            );
        }

        // Redirect back with success message
        return redirect()->route('admin.notification.index')->with('success', 'Notification created successfully.');
    }


}
