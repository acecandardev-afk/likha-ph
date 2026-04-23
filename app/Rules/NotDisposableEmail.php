<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotDisposableEmail implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (! is_string($value) || ! str_contains($value, '@')) {
            return true;
        }

        $domain = strtolower((string) substr(strrchr($value, '@'), 1));
        if ($domain === '') {
            return false;
        }

        $blocked = config('signup.disposable_domains', []);

        return ! in_array($domain, $blocked, true);
    }

    public function message(): string
    {
        return 'Please use a permanent email address, not a disposable or temporary inbox.';
    }
}
