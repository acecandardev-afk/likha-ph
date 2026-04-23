<?php

namespace App\Support;

use App\Models\City;
use Illuminate\Validation\Rule;

class Guihulngan
{
    public static function cityCode(): string
    {
        return (string) config('guihulngan.city_code', '074611');
    }

    /**
     * Guihulngan City (single delivery locality). Null if not seeded in DB.
     */
    public static function deliveryCity(): ?City
    {
        return City::query()
            ->where('code', self::cityCode())
            ->with(['province.region'])
            ->first();
    }

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
