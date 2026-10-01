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

    /*
    | Sito plus.welfarenest.it. after_activation_url e' dove atterra chi ha appena
    | attivato l'account: lasciarlo vuoto lo porta all'area riservata sul CRM.
    | Va valorizzato con l'indirizzo del sito che avvia il login OIDC, quando esiste.
    */
    'wn_plus_site' => [
        // Pagina interna del sito da cui si entra nell'area riservata (quella che
        // avvia il login OIDC), non la home: dalla home l'utente resterebbe anonimo.
        // Finché è vuota, il portale non mostra il pulsante verso il sito.
        'area_url' => env('WN_PLUS_SITE_AREA_URL'),

        // Dove atterra chi ha appena attivato l'account. Se vuota si usa area_url,
        // e in mancanza anche di quella l'area riservata sul CRM.
        'after_activation_url' => env('WN_PLUS_AFTER_ACTIVATION_URL'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
