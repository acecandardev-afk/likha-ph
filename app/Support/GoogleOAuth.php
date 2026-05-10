<?php

namespace App\Support;

/**
 * Resolves Google OAuth credentials from config and from the real environment.
 *
 * On production, php artisan config:cache bakes .env at cache time; later edits to .env are ignored
 * until the cache is rebuilt. Some hosts also inject GOOGLE_* via the web server / php-fpm pool only,
 * which Laravel's cached config may not include. This class falls back to getenv/$_ENV/$_SERVER.
 */
class GoogleOAuth
{
    public static function isConfigured(): bool
    {
        return self::resolvedClientId() !== '' && self::resolvedClientSecret() !== '';
    }

    public static function resolvedClientId(): string
    {
        return self::resolve('GOOGLE_CLIENT_ID', 'services.google.client_id');
    }

    public static function resolvedClientSecret(): string
    {
        return self::resolve('GOOGLE_CLIENT_SECRET', 'services.google.client_secret');
    }

    private static function resolve(string $envKey, string $configKey): string
    {
        $fromConfig = config($configKey);
        if (self::nonEmptyString($fromConfig)) {
            return trim((string) $fromConfig);
        }

        $fromEnv = self::envValue($envKey);

        return $fromEnv !== null ? trim($fromEnv) : '';
    }

    private static function envValue(string $key): ?string
    {
        if (isset($_ENV[$key]) && is_string($_ENV[$key]) && self::nonEmptyString($_ENV[$key])) {
            return $_ENV[$key];
        }

        if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && self::nonEmptyString($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        $v = getenv($key);
        if (is_string($v) && self::nonEmptyString($v)) {
            return $v;
        }

        return null;
    }

    private static function nonEmptyString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
