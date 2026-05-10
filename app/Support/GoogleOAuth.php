<?php

namespace App\Support;

class GoogleOAuth
{
    public static function isConfigured(): bool
    {
        return self::nonEmptyConfigString(config('services.google.client_id'))
            && self::nonEmptyConfigString(config('services.google.client_secret'));
    }

    private static function nonEmptyConfigString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
