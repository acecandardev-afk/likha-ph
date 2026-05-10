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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Google OAuth (Laravel Socialite) — sign-in with Google on login/register UI.
    |
    | 1. Google Cloud Console → APIs & Services → Credentials → Create Credentials → OAuth client ID
    |    → Application type: Web application.
    | 2. Authorized JavaScript origins: https://your-domain.com (e.g. https://likha-ph.shop)
    | 3. Authorized redirect URIs (must match exactly — trailing slash matters):
    |      https://your-domain.com/auth/google/callback
    |    Or set GOOGLE_REDIRECT_URI in .env to that full URL.
    | 4. OAuth consent screen: publish app or add test users while in Testing.
    | 5. Copy Client ID and Client Secret into .env:
    |      GOOGLE_CLIENT_ID=....apps.googleusercontent.com
    |      GOOGLE_CLIENT_SECRET=GOCSPX-...
    |      APP_URL must match the origin you registered (Socialite uses request URL when GOOGLE_REDIRECT_URI is unset).
    */
    'google' => [
        'client_id' => trim((string) env('GOOGLE_CLIENT_ID', '')),
        'client_secret' => trim((string) env('GOOGLE_CLIENT_SECRET', '')),
        // When set, must match an "Authorized redirect URI" in Google Cloud Console exactly.
        // When empty, GoogleAuthController builds the callback from the current request (avoids APP_URL/port/path mismatches).
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim((string) env('APP_URL', 'http://localhost'), '/').'/auth/google/callback'),
        'redirect_uri_explicit' => env('GOOGLE_REDIRECT_URI'),
    ],

];
