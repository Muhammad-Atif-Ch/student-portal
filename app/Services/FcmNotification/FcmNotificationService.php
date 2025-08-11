<?php

namespace App\Services\FcmNotification;

use Illuminate\Support\Facades\Http;
use Google\Client;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    private $serverKey;
    private $endpoint;  
    protected $googleAuth;
    protected $serviceAccount;
    protected $projectId;

    public function __construct()
    {
        $this->serverKey = env('FCM_SERVER_KEY');
        $this->endpoint = "https://fcm.googleapis.com/fcm/send";

        $this->serviceAccount = json_decode(
            file_get_contents(storage_path('app/private/dtt-ireland-bd355-firebase-adminsdk-fbsvc-602d350472.json')),
            true
        );

        $this->projectId = $this->serviceAccount['project_id'];
    }

    public function sendFcmNotification($deviceToken, $title, $body, $data = [])
    {
        $accessToken = $this->getAccessToken();

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $payload = [
            'message' => [
                'token'        => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body
                ]
            ]
        ];

        $response = Http::withToken($accessToken)
            ->post($url, $payload);

        return $response->json();
    
    }

    public function getAccessToken()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path("app/private/dtt-ireland-bd355-firebase-adminsdk-fbsvc-602d350472.json"));
        $client->addScope('https://www.googleapis.com/auth/cloud-platform');
        $client->setSubject(null); // Usually not needed for service accounts

        // Configure timeouts and retries for better network handling
        $client->setHttpClient(new \GuzzleHttp\Client([
        'timeout' => 30,
        'connect_timeout' => 10,
        'retry_on_status' => [408, 429, 500, 502, 503, 504],
        'max_retry_attempts' => 3,
        ]));

        // Fetch the access token
        $tokenArray = $client->fetchAccessTokenWithAssertion();
        return $tokenArray['access_token'];
    }
}