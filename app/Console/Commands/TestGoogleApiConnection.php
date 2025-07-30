<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Google\Client;

class TestGoogleApiConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:test-connection';

    protected $description = 'Test Google API connectivity and diagnose network issues';

    public function handle()
    {
        $this->info('Testing Google API connectivity...');

        // Test 1: Basic HTTP connectivity
        $this->info('1. Testing basic HTTP connectivity...');
        try {
            $response = Http::timeout(10)->get('https://www.googleapis.com');
            if ($response->successful()) {
                $this->info('✓ Basic HTTP connectivity: OK');
            } else {
                $this->error('✗ Basic HTTP connectivity: Failed');
            }
        } catch (\Exception $e) {
            $this->error('✗ Basic HTTP connectivity: ' . $e->getMessage());
        }

        // Test 2: OAuth2 endpoint
        $this->info('2. Testing OAuth2 endpoint...');
        try {
            $response = Http::timeout(10)->get('https://oauth2.googleapis.com/token');
            if ($response->status() === 400) {
                $this->info('✓ OAuth2 endpoint: Accessible (400 is expected for GET without auth)');
            } else {
                $this->warn('⚠ OAuth2 endpoint: Unexpected response code ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('✗ OAuth2 endpoint: ' . $e->getMessage());
        }

        // Test 3: Google Client authentication
        $this->info('3. Testing Google Client authentication...');
        try {
            $client = new Client();
            $client->setAuthConfig(storage_path('app/private/google-service-account.json'));
            $client->addScope('https://www.googleapis.com/auth/androidpublisher');
            
            $client->setHttpClient(new \GuzzleHttp\Client([
                'timeout' => 30,
                'connect_timeout' => 10,
            ]));

            $tokenArray = $client->fetchAccessTokenWithAssertion();
            
            if (isset($tokenArray['access_token'])) {
                $this->info('✓ Google Client authentication: OK');
                $this->info('  Access token length: ' . strlen($tokenArray['access_token']));
            } else {
                $this->error('✗ Google Client authentication: No access token received');
            }
        } catch (\Exception $e) {
            $this->error('✗ Google Client authentication: ' . $e->getMessage());
        }

        // Test 4: Android Publisher API
        $this->info('4. Testing Android Publisher API...');
        try {
            $client = new Client();
            $client->setAuthConfig(storage_path('app/private/google-service-account.json'));
            $client->addScope('https://www.googleapis.com/auth/androidpublisher');
            
            $tokenArray = $client->fetchAccessTokenWithAssertion();
            $accessToken = $tokenArray['access_token'];
            
            $packageName = config('google-play.package_name');
            $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}";
            
            $response = Http::timeout(30)
                ->withToken($accessToken)
                ->get($url);
                
            if ($response->status() === 200) {
                $this->info('✓ Android Publisher API: OK');
            } elseif ($response->status() === 403) {
                $this->warn('⚠ Android Publisher API: Access denied (check service account permissions)');
            } else {
                $this->warn('⚠ Android Publisher API: Response code ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('✗ Android Publisher API: ' . $e->getMessage());
        }

        $this->info('Network connectivity test completed.');
    }
} 