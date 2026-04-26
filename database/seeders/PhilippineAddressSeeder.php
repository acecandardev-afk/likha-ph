<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Services\AddressService;
use Illuminate\Support\Facades\Cache;

class PhilippineAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataset = [
            [
                'code' => '07',
                'name' => 'Central Visayas',
                'provinces' => [
                    [
                        'code' => '0746',
                        'name' => 'Negros Oriental',
                        'cities' => [
                            [
                                'code' => '074611',
                                'name' => 'Guihulngan',
                                'barangays' => [
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
                                ],
                            ],
                            [
                                'code' => '074610',
                                'name' => 'Dumaguete',
                                'barangays' => [
                                    'Bagacay' => '074610001',
                                    'Batinguel' => '074610002',
                                    'Bunao' => '074610003',
                                    'Calindagan' => '074610004',
                                    'Daro' => '074610005',
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => '0722',
                        'name' => 'Cebu',
                        'cities' => [
                            [
                                'code' => '072217',
                                'name' => 'Cebu City',
                                'barangays' => [
                                    'Lahug' => '072217001',
                                    'Mabolo' => '072217002',
                                    'Guadalupe' => '072217003',
                                    'Banilad' => '072217004',
                                    'Talamban' => '072217005',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => '13',
                'name' => 'National Capital Region',
                'provinces' => [
                    [
                        'code' => '1339',
                        'name' => 'Metro Manila',
                        'cities' => [
                            [
                                'code' => '133901',
                                'name' => 'Quezon City',
                                'barangays' => [
                                    'Commonwealth' => '133901001',
                                    'Batasan Hills' => '133901002',
                                    'Holy Spirit' => '133901003',
                                    'Tandang Sora' => '133901004',
                                    'Bagumbayan' => '133901005',
                                ],
                            ],
                            [
                                'code' => '133902',
                                'name' => 'Manila',
                                'barangays' => [
                                    'Ermita' => '133902001',
                                    'Malate' => '133902002',
                                    'Paco' => '133902003',
                                    'Sampaloc' => '133902004',
                                    'Tondo' => '133902005',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => '11',
                'name' => 'Davao Region',
                'provinces' => [
                    [
                        'code' => '1124',
                        'name' => 'Davao del Sur',
                        'cities' => [
                            [
                                'code' => '112402',
                                'name' => 'Davao City',
                                'barangays' => [
                                    'Buhangin' => '112402001',
                                    'Matina' => '112402002',
                                    'Talomo' => '112402003',
                                    'Toril' => '112402004',
                                    'Bunawan' => '112402005',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => '06',
                'name' => 'Western Visayas',
                'provinces' => [
                    [
                        'code' => '0630',
                        'name' => 'Iloilo',
                        'cities' => [
                            [
                                'code' => '063022',
                                'name' => 'Iloilo City',
                                'barangays' => [
                                    'Jaro' => '063022001',
                                    'Mandurriao' => '063022002',
                                    'Molo' => '063022003',
                                    'La Paz' => '063022004',
                                    'City Proper' => '063022005',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($dataset as $regionData) {
            $region = Region::updateOrCreate(
                ['code' => $regionData['code']],
                ['name' => $regionData['name']]
            );

            foreach ($regionData['provinces'] as $provinceData) {
                $province = Province::updateOrCreate(
                    ['code' => $provinceData['code']],
                    ['name' => $provinceData['name'], 'region_id' => $region->id]
                );

                foreach ($provinceData['cities'] as $cityData) {
                    $city = City::updateOrCreate(
                        ['code' => $cityData['code']],
                        ['name' => $cityData['name'], 'province_id' => $province->id]
                    );

                    foreach ($cityData['barangays'] as $barangayName => $barangayCode) {
                        Barangay::updateOrCreate(
                            ['code' => $barangayCode],
                            ['name' => $barangayName, 'city_id' => $city->id]
                        );
                    }
                }
            }
        }

        AddressService::forgetClientBootstrapCache();
        Cache::forget('regions');
    }
}
