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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    | Endpoint consumati dal sito plus.welfarenest.it. Il token vuoto non apre
    | l'accesso: il middleware chiude con 503 (vedi VerifyWnPlusApiToken).
    | WN_PLUS_API_ALLOWED_IPS e' un elenco separato da virgole, vuoto = nessun
    | filtro per IP.
    */
    'wn_plus_api' => [
        'token' => env('WN_PLUS_API_TOKEN'),
        'allowed_ips' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('WN_PLUS_API_ALLOWED_IPS', ''))
        ))),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
