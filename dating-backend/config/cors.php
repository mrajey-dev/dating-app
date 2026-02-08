<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    // Apply CORS to these paths (all API routes)
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Allowed HTTP methods (GET, POST, PUT, DELETE, etc.)
    'allowed_methods' => ['*'],

    // Allowed origins (your frontend URL)
  'allowed_origins' => ['*'],

    // Patterns for origins
    'allowed_origins_patterns' => [],

    // Allowed request headers
    'allowed_headers' => ['*'],

    // Exposed headers in the response
    'exposed_headers' => [],

    // Max age for preflight requests (seconds)
    'max_age' => 0,

    // Whether to allow cookies/auth credentials
    'supports_credentials' => false,

];
