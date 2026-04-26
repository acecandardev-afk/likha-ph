<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ArtisanProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

            $filename = 'seed_artisan_'.Str::slug($user->email).'.svg';
            Storage::disk('artisans')->put($filename, $this->makeArtisanSeedSvg($user->name, $artisanData['profile']['workshop_name']));

            $profilePayload = $artisanData['profile'];
            $profilePayload['profile_image'] = $filename;

            ArtisanProfile::updateOrCreate(
                ['user_id' => $user->id],
                $profilePayload
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

    private function makeArtisanSeedSvg(string $name, string $workshop): string
    {
        $initials = collect(explode(' ', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        $safeWorkshop = e($workshop);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 400" role="img" aria-label="{$safeWorkshop}">
  <defs>
    <linearGradient id="bg" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="#6e4f3f"/>
      <stop offset="100%" stop-color="#2f241e"/>
    </linearGradient>
  </defs>
  <rect width="640" height="400" fill="url(#bg)"/>
  <circle cx="320" cy="150" r="72" fill="#f6ede4" opacity="0.95"/>
  <text x="320" y="170" text-anchor="middle" fill="#2f241e" font-family="Arial, sans-serif" font-size="54" font-weight="700">{$initials}</text>
  <text x="320" y="295" text-anchor="middle" fill="#f9f4ef" font-family="Arial, sans-serif" font-size="30" font-weight="600">{$safeWorkshop}</text>
  <text x="320" y="330" text-anchor="middle" fill="#e7dacc" font-family="Arial, sans-serif" font-size="18">Guihulngan Artisan</text>
</svg>
SVG;
    }
}