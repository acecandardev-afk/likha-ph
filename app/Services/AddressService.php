<?php

namespace App\Services;

use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use Illuminate\Support\Facades\Cache;

class AddressService
{
    public const CACHE_KEY_BOOTSTRAP = 'ph.address.client_bootstrap_v2';

    /**
     * Full hierarchy for inline JS: one payload per page, no waterfall of /api/region/... requests.
     * Plain arrays only (never cache Eloquent models — file/cache serialization can 500 in production).
     */
    public function getClientBootstrap(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY_BOOTSTRAP, 3600, function () {
                return [
                    'regions' => Region::query()
                        ->orderBy('name')
                        ->get(['id', 'name', 'code'])
                        ->toArray(),
                    'provinces' => Province::query()
                        ->orderBy('name')
                        ->get(['id', 'name', 'code', 'region_id'])
                        ->toArray(),
                    'cities' => City::query()
                        ->orderBy('name')
                        ->get(['id', 'name', 'code', 'province_id'])
                        ->toArray(),
                    'barangays' => Barangay::query()
                        ->orderBy('name')
                        ->get(['id', 'name', 'code', 'city_id'])
                        ->toArray(),
                ];
            });
        } catch (\Throwable $e) {
            report($e);

            return [
                'regions' => [],
                'provinces' => [],
                'cities' => [],
                'barangays' => [],
            ];
        }
    }

    public static function forgetClientBootstrapCache(): void
    {
        Cache::forget(self::CACHE_KEY_BOOTSTRAP);
    }

    public function getRegions()
    {
        return Cache::remember('regions', 3600, function () {
            return Region::orderBy('name')->get();
        });
    }

    public function getProvinces($regionId = null)
    {
        $cacheKey = 'provinces' . ($regionId ? "_region_{$regionId}" : '');
        return Cache::remember($cacheKey, 3600, function () use ($regionId) {
            $query = Province::orderBy('name');
            if ($regionId) {
                $query->where('region_id', $regionId);
            }
            return $query->get();
        });
    }

    public function getCities($provinceId = null)
    {
        $cacheKey = 'cities' . ($provinceId ? "_province_{$provinceId}" : '');
        return Cache::remember($cacheKey, 3600, function () use ($provinceId) {
            $query = City::orderBy('name');
            if ($provinceId) {
                $query->where('province_id', $provinceId);
            }
            return $query->get();
        });
    }

    public function getBarangays($cityId = null)
    {
        $cacheKey = 'barangays' . ($cityId ? "_city_{$cityId}" : '');
        return Cache::remember($cacheKey, 3600, function () use ($cityId) {
            $query = Barangay::orderBy('name');
            if ($cityId) {
                $query->where('city_id', $cityId);
            }
            return $query->get();
        });
    }

    public function clearCache()
    {
        Cache::forget('regions');
        Cache::forget('provinces');
        Cache::forget('cities');
        Cache::forget('barangays');
        self::forgetClientBootstrapCache();
    }
}