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
        // UserSeeder runs AdminSeeder first (admin + Philippine addresses), then demo artisans/customers.
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            // OrderSeeder::class, // Optional: uncomment for test orders
        ]);
    }
}
