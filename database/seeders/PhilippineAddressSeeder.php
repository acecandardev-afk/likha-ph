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
        $region = Region::updateOrCreate(['code' => '07'], ['name' => 'Central Visayas']);

        $province = Province::updateOrCreate(['code' => '0746'], ['name' => 'Negros Oriental', 'region_id' => $region->id]);

        $city = City::updateOrCreate(['code' => '074611'], ['name' => 'Guihulngan', 'province_id' => $province->id]);

        // Barangays for Guihulngan
        $barangays = [
            'Bakid' => '074611001',
            'Balogo' => '074611002',
            'Banwaque' => '074611003',
            'Basak' => '074611004',
            'Binobohan' => '074611005',
            'Buenavista' => '074611006',
            'Bulado' => '074611007',
            'Calamba' => '074611008',
            'Calupa-an' => '074611009',
            'Hibaiyo' => '074611010',
            'Hilaitan' => '074611011',
            'Hinakpan' => '074611012',
            'Humayhumay' => '074611013',
            'Imelda' => '074611014',
            'Kagawasan' => '074611015',
            'Linantuyan' => '074611016',
            'Luz' => '074611017',
            'Mabunga' => '074611018',
            'Magsaysay' => '074611019',
            'Malusay' => '074611020',
            'Maniak' => '074611021',
            'McKinley' => '074611022',
            'Nagsaha' => '074611023',
            'Padre Zamora' => '074611024',
            'Plagatasanon' => '074611025',
            'Planas' => '074611026',
            'Poblacion' => '074611027',
            'Sandayao' => '074611028',
            'Tacpao' => '074611029',
            'Tinayunan Beach' => '074611030',
            'Tinayunan Hill' => '074611031',
            'Trinidad' => '074611032',
            'Villegas' => '074611033',
        ];

        foreach ($barangays as $name => $code) {
            Barangay::updateOrCreate(['code' => $code], ['name' => $name, 'city_id' => $city->id]);
        }
    }
}
