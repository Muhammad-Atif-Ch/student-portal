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

  'package_name' => env('GOOGLE_PLAY_PACKAGE_NAME', 'com.ddt_car_bike_ireland'),

  'api' => [
    'base_url' => 'https://androidpublisher.googleapis.com/androidpublisher/v3',
  ],

  'subscription_id' => 'basic_subscription',
];
