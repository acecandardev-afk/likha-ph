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
            ->where(function ($query) {
                $query
                    ->where('code', self::cityCode())
                    ->orWhereIn('name', ['Guihulngan', 'Guihulngan City']);
            })
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

    /**
     * Rules when the form posts a barangay **id** from the address cascade (register/apply artisan).
     * Profile still stores the barangay **name** string.
     *
     * @return list<\Closure|\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function artisanBarangayIdRules(): array
    {
        $city = self::deliveryCity();
        if (! $city) {
            return ['required', 'integer', 'exists:barangays,id'];
        }

        return [
            'required',
            'integer',
            Rule::exists('barangays', 'id')->where(fn ($q) => $q->where('city_id', $city->id)),
        ];
    }

    /**
     * Same as artisan barangay id rule, but barangay may be omitted (e.g. account / partial address).
     *
     * @return list<\Closure|\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function guihulnganBarangayIdRulesOptional(): array
    {
        $city = self::deliveryCity();
        if (! $city) {
            return ['nullable', 'integer', 'exists:barangays,id'];
        }

        return [
            'nullable',
            'integer',
            Rule::exists('barangays', 'id')->where(fn ($q) => $q->where('city_id', $city->id)),
        ];
    }
}
