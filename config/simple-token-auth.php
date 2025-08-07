<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Tokens
    |--------------------------------------------------------------------------
    |
    | A list of all the API tokens that are allowed to access the application.
    | You can define named tokens for different services for easier
    | identification and rotation.
    |
    | Example:
    | 'tokens' => [
    |     'service-name' => env('API_TOKEN_SERVICE_NAME'),
    |     'another-service' => env('API_TOKEN_ANOTHER_SERVICE'),
    | ],
    |
    */
    'tokens' => [
        // 'service-name' => env('API_TOKEN_SERVICE_NAME'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Token
    |--------------------------------------------------------------------------
    |
    | A single, general-purpose fallback token that can be used if no service-
    | specific token is provided or matched. This is useful for backward
    | compatibility or for simpler use cases.
    |
    */
    'fallback_token' => env('API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting to protect your application from brute-force
    | attacks. You can enable or disable it, and set the maximum number of
    | attempts and the lockout duration in seconds.
    |
    */
    'rate_limiting' => [
        'enabled' => env('API_RATE_LIMITING_ENABLED', true),
        'max_attempts' => env('API_RATE_LIMITING_MAX_ATTEMPTS', 60),
        'lockout_duration' => env('API_RATE_LIMITING_LOCKOUT_DURATION', 60), // in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Define whether to log failed authentication attempts. When enabled, it
    | will record the IP address and the endpoint that was accessed.
    |
    */
    'log_failed_attempts' => env('API_LOG_FAILED_ATTEMPTS', true),
];
