<?php

namespace App\Services\FcmNotification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    private $serverKey;
    private $endpoint;  

    public function __construct()
    {
        $this->serverKey = env('FCM_SERVER_KEY');
        $this->endpoint = "https://fcm.googleapis.com/fcm/send";
    }

    public function sendFcmNotification($deviceToken, $title, $body, $data = [])
    {
        $payload = [
            "to" => $deviceToken,
            "notification" => [
                "title" => $title,
                "body" => $body,
                "sound" => "default"
            ],
        ];

        $response = Http::withHeaders([
            "Authorization" => "key=$this->serverKey",
            "Content-Type" => "application/json"
        ])->post($this->endpoint, $payload);

        return $response->body();
    }
}