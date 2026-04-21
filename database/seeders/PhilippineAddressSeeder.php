<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

class PhilippineAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample data - in production, load full Philippine address data
        $region = Region::create(['name' => 'Central Visayas', 'code' => '07']);

        $province = Province::create(['name' => 'Negros Oriental', 'code' => '0746', 'region_id' => $region->id]);

        $city = City::create(['name' => 'Guihulngan', 'code' => '074611', 'province_id' => $province->id]);

        // Sample barangays
        Barangay::create(['name' => 'Bakid', 'code' => '074611001', 'city_id' => $city->id]);
        Barangay::create(['name' => 'Balogo', 'code' => '074611002', 'city_id' => $city->id]);
        Barangay::create(['name' => 'Banwaque', 'code' => '074611003', 'city_id' => $city->id]);
        // Add more as needed
    }
}
