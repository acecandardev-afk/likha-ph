<?php

/**
 * Guihulngan City (Negros Oriental) — 33 barangays (PSA / LGU).
 * Alphabetically sorted for select lists.
 */
return [

    /**
     * PSGC city code for `cities` table (seeder: City::updateOrCreate(['code' => ...]).
     * Delivery is always this city; checkout shows only its barangays.
     */
    'city_code' => '074611',

    'city_name' => 'Guihulngan City',
    'province' => 'Negros Oriental',

    'barangays' => [
        'Bakid',
        'Balogo',
        'Banwaque',
        'Basak',
        'Binobohan',
        'Buenavista',
        'Bulado',
        'Calamba',
        'Calupa-an',
        'Hibaiyo',
        'Hilaitan',
        'Hinakpan',
        'Humayhumay',
        'Imelda',
        'Kagawasan',
        'Linantuyan',
        'Luz',
        'Mabunga',
        'Magsaysay',
        'Malusay',
        'Maniak',
        'McKinley',
        'Nagsaha',
        'Padre Zamora',
        'Plagatasanon',
        'Planas',
        'Poblacion',
        'Sandayao',
        'Tacpao',
        'Tinayunan Beach',
        'Tinayunan Hill',
        'Trinidad',
        'Villegas',
    ],
];
