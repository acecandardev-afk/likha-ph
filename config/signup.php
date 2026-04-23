<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MX (DNS) validation for sign-up email domains
    |--------------------------------------------------------------------------
    |
    | When true, the email domain must have valid DNS/MX records. Requires the
    | PHP intl extension. Set to false in environments with no DNS (e.g. some
    | CI) or if you see false rejections for valid mail providers.
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
