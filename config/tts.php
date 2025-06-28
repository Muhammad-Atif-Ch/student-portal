<?php

return [
  /*
  |--------------------------------------------------------------------------
  | Text-to-Speech Configuration
  |--------------------------------------------------------------------------
  |
  | This file contains the configuration settings for the Text-to-Speech service.
  |
  */

  /*
  |--------------------------------------------------------------------------
  | Preferred Voice Gender
  |--------------------------------------------------------------------------
  |
  | This value determines the default gender for the TTS voice.
  | Supported values: 'female', 'male'
  |
  */
  'preferred_gender' => env('TTS_PREFERRED_GENDER', 'male'),

  /*
  |--------------------------------------------------------------------------
  | Azure TTS Configuration
  |--------------------------------------------------------------------------
  |
  | Configuration settings specific to Azure's Text-to-Speech service.
  |
  */
  'azure' => [
    'key' => env('AZURE_TTS_KEY'),
    'region' => env('AZURE_TTS_REGION', 'eastus'),
    'endpoint' => env('AZURE_TTS_ENDPOINT'),
  ],
];