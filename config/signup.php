<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MX (DNS) validation for sign-up email domains
    |--------------------------------------------------------------------------
    |
    | When true (and PHP intl is loaded), the email domain must have valid
    | DNS/MX records. Spoofing checks also require intl; without intl, signup
    | falls back to RFC validation only. Set to false if you see false
    | rejections or have no outbound DNS from the app server.
    |
    */

    'validate_mx' => env('SIGNUP_EMAIL_VALIDATE_MX', true),

    /*
    |--------------------------------------------------------------------------
    | Disposable / throwaway domains (lowercase, no @)
    |--------------------------------------------------------------------------
    */

    'disposable_domains' => array_values(array_unique(array_filter(array_merge(
        [
            'mailinator.com',
            'guerrillamail.com',
            'guerrillamail.org',
            '10minutemail.com',
            '10minutemail.net',
            'throwaway.email',
            'tempmail.com',
            'temp-mail.org',
            'yopmail.com',
            'trashmail.com',
            'fakeinbox.com',
            'dispostable.com',
            'maildrop.cc',
            'getnada.com',
            'sharklasers.com',
            'trashmail.net',
            'emailondeck.com',
            'mailnesia.com',
            'mintemail.com',
            'trashmail.de',
        ],
        array_map('strtolower', array_map('trim', explode(',', (string) env('SIGNUP_BLOCKED_EMAIL_DOMAINS', ''))))
    )))),

];
