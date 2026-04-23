<?php

namespace App\Services;

use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use Illuminate\Support\Facades\Cache;

class AddressService
{
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
        // Also clear specific caches if needed, but for simplicity, clear all
    }
}