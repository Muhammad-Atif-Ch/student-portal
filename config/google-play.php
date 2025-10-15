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
    'iap_url_production' => 'https://buy.itunes.apple.com/verifyReceipt',
    'iap_url_sandbox' => 'https://sandbox.itunes.apple.com/verifyReceipt',
  ],

  'subscription_id' => 'basic_subscription',
  'shared_secret' => env('IOS_SHARED_SECRET', 'null'),


];
