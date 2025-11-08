<?php

return [
  /*
  |--------------------------------------------------------------------------
  | Google Play Configuration
  |--------------------------------------------------------------------------
  |
  | This file contains the configuration for Google Play API integration
  |
  */

  'package_name' => env('GOOGLE_PLAY_PACKAGE_NAME', 'com.dtt_car_bike_ireland'),

  'api' => [
    'base_url' => 'https://androidpublisher.googleapis.com/androidpublisher/v3',
    // 'iap_url_production' => 'https://buy.itunes.apple.com/verifyReceipt',
    // 'iap_url_sandbox' => 'https://sandbox.itunes.apple.com/verifyReceipt',
    'apple_url' => env('APPLE_URL', 'null'),
  ],

  'subscription_id' => 'basic_subscription',
  // 'shared_secret' => env('IOS_SHARED_SECRET', 'null'),

  'apple_issuer_id' => env('APPLE_ISSUER_ID', 'null'),
  'apple_key_id' => env('APPLE_KEY_ID', 'null'),
  'apple_bundle_id' => env('APPLE_BUNDLE_ID', 'null'),
  'bundle_id' => env('APPLE_BUNDLE_ID', 'null'),
  'apple_private_key' => "-----BEGIN PRIVATE KEY-----
MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQgFOsggXzvsnNoVbMy
32VZm8SsCzYcIMgUcc2EWsj5LGqgCgYIKoZIzj0DAQehRANCAAT0KIq/1K+EB51P
lgZiW+pbQjA2/kDsHQ4cwTvDHBitBtt7YzCF9+RUBTDrHgJ9IBqCo7y+GO7LDe0Z
ij4XRRBe
-----END PRIVATE KEY-----",

];
