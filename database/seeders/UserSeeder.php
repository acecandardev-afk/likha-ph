<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ArtisanProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminSeeder::class);

        $this->command->info('✓ Run AdminSeeder for credentials (or SEED_ADMIN_* in .env).');

        // 1. Create Sample Artisans
        $artisans = [
            [
                'user' => [
                    'name' => 'Maria Santos',
                    'email' => 'maria.santos@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'artisan',
                    'phone' => '+63 917 123 4567',
                    'address' => 'Barangay Poblacion, Guihulngan City',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'workshop_name' => 'Santos Bamboo Crafts',
                    'story' => 'For three generations, the Santos family has been crafting beautiful bamboo products. We use sustainable harvesting methods and traditional techniques passed down from our ancestors. Each piece tells a story of our heritage and dedication to preserving Filipino craftsmanship.',
                    'barangay' => 'Poblacion',
                ],
            ],
            [
                'user' => [
                    'name' => 'Pedro Reyes',
                    'email' => 'pedro.reyes@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'artisan',
                    'phone' => '+63 918 234 5678',
                    'address' => 'Barangay Magsaysay, Guihulngan City',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'workshop_name' => 'Reyes Abaca Weaving',
                    'story' => 'Our workshop specializes in handwoven abaca products. We work with local farmers to source the finest abaca fibers, supporting our community while creating eco-friendly, durable products. Every item is woven with care and precision.',
                    'barangay' => 'Magsaysay',
                ],
            ],
            [
                'user' => [
                    'name' => 'Luz Garcia',
                    'email' => 'luz.garcia@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'artisan',
                    'phone' => '+63 919 345 6789',
                    'address' => 'Barangay Tacpao, Guihulngan City',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'workshop_name' => 'Garcia Native Baskets',
                    'story' => 'My grandmother taught me the art of basket weaving when I was just seven years old. Now I teach young women in our community, ensuring this beautiful tradition continues. Each basket is a labor of love, perfect for modern homes while honoring our past.',
                    'barangay' => 'Tacpao',
                ],
            ],
            [
                'user' => [
                    'name' => 'Roberto Cruz',
                    'email' => 'roberto.cruz@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'artisan',
                    'phone' => '+63 920 456 7890',
                    'address' => 'Barangay Sandayao, Guihulngan City',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
                'profile' => [
                    'workshop_name' => 'Cruz Handicraft Studio',
                    'story' => 'We create unique homeware items using natural materials from our region. Our mission is to showcase the beauty of Guihulngan craftsmanship to the world while providing sustainable livelihoods to our artisan community.',
                    'barangay' => 'Sandayao',
                ],
            ],
        ];

        foreach ($artisans as $artisanData) {
            $user = User::firstOrCreate(
                ['email' => $artisanData['user']['email']],
                $artisanData['user']
            );

            ArtisanProfile::updateOrCreate(
                ['user_id' => $user->id],
                $artisanData['profile']
            );

            $this->command->info("✓ Artisan created: {$user->email} ({$artisanData['profile']['workshop_name']})");
        }

        // 3. Create Sample Customers
        $customers = [
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+63 921 567 8901',
                'address' => 'Dumaguete City, Negros Oriental',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Anna Reyes',
                'email' => 'anna.reyes@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+63 922 678 9012',
                'address' => 'Cebu City, Cebu',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Michael Tan',
                'email' => 'michael.tan@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+63 923 789 0123',
                'address' => 'Manila City, Metro Manila',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = User::firstOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );
            $this->command->info("✓ Customer created: {$customer->email}");
        }

        $this->command->info('');
        $this->command->warn('Default password for all test users: password');
        $this->command->warn('Admin uses SEED_ADMIN_PASSWORD in .env (default Admin@2026) — see AdminSeeder.');
    }
}