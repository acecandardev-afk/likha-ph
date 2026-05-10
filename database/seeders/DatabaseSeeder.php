<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default seed: admin user only (AdminSeeder also runs PhilippineAddressSeeder for checkout/addresses).
        // Demo data: php artisan db:seed --class=UserSeeder (includes AdminSeeder first), CategorySeeder, ProductSeeder, etc.
        $this->call([
            AdminSeeder::class,
        ]);
    }
}
