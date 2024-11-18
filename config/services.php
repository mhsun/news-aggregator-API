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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'newsapiorg' => [
        'v1' => [
            'url' => 'https://newsapi.org/v2/everything',
            'page_size' => 100,
        ],
        'api_key' => env('NEWSAPI_KEY'),
    ],

    'the_guardian' => [
        'v1' => [
            'url' => 'https://content.guardianapis.com/search',
            'page_size' => 200,
        ],
        'api_key' => env('THE_GUARDIAN_KEY'),
    ],

    'ny_times' => [
        'v1' => [
            'url' => 'https://api.nytimes.com/svc/search/v2/articlesearch.json',
        ],
        'api_key' => env('NY_TIMES_KEY'),
    ],

];
