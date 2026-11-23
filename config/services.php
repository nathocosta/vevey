<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
        'from' => env('RESEND_FROM', 'Vevey Doces <onboarding@resend.dev>'),
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

    'melhor_envio' => [
        'base_url' => env('MELHOR_ENVIO_BASE_URL', 'https://sandbox.melhorenvio.com.br'),
        'token' => env('MELHOR_ENVIO_TOKEN'),
        'origin_cep' => env('STORE_ORIGIN_CEP', '01310100'),
        'user_agent' => env('MELHOR_ENVIO_USER_AGENT', 'VeveyDoces (contato@veveydoces.com.br)'),
    ],

];
