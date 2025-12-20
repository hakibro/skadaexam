<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sisda' => [
        'base_url' => env('SISDA_API_BASE_URL', 'https://api.daruttaqwa.or.id/sisda/v1'),
        'timeout' => env('SISDA_API_TIMEOUT', 15),
        'retry_times' => env('SISDA_API_RETRY_TIMES', 2),
        'retry_delay' => env('SISDA_API_RETRY_DELAY', 1000),
        'user_agent' => env('SISDA_USER_AGENT', 'SKADA-Exam-System/1.0'),
    ],
    'chrome' => [
        'path' => env('CHROME_PATH', '/usr/bin/google-chrome'),
        'args' => array_filter(explode(',', env('CHROME_ARGS', '--no-sandbox'))),
    ],
];
