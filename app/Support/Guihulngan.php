<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class Guihulngan
{
    /**
     * @return list<string>
     */
    public static function barangays(): array
    {
        return config('guihulngan.barangays', []);
    }

    /**
     * Validation rules for a barangay field (exact match to config list).
     *
     * @return list<\Closure|\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function barangayRules(bool $required = true): array
    {
        $in = Rule::in(self::barangays());

        return $required
            ? ['required', 'string', $in]
            : ['nullable', 'string', $in];
    }
}
