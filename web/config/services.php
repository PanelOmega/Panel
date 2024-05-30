<?php

use App\OmegaConfig;

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
        'token' => OmegaConfig::get('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => OmegaConfig::get('AWS_ACCESS_KEY_ID'),
        'secret' => OmegaConfig::get('AWS_SECRET_ACCESS_KEY'),
        'region' => OmegaConfig::get('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => OmegaConfig::get('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => OmegaConfig::get('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => OmegaConfig::get('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
