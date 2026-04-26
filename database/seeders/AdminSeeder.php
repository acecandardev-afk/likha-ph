<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a single admin user. Use this after `migrate:fresh` when you only want
 * the admin (no sample artisans/products), e.g.:
 *
 *   php artisan migrate:fresh
 *   php artisan db:seed --class=AdminSeeder
 *
 * Set SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD in .env to control credentials
 * (password is plain text; it is hashed on seed).
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Keep address-dependent flows (artisan apply / checkout) working even when
        // only AdminSeeder is executed on a fresh environment (e.g. Render shell).
        $this->call(PhilippineAddressSeeder::class);

        $email = (string) env('SEED_ADMIN_EMAIL', 'admin@guihulngan-handicrafts.local');
        $password = (string) env('SEED_ADMIN_PASSWORD', 'Admin@2026');

        // Ensure sample data keeps exactly one admin account.
        User::query()
            ->where('role', 'admin')
            ->where('email', '!=', $email)
            ->update(['role' => 'customer']);

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('SEED_ADMIN_NAME', 'System Administrator'),
                'password' => Hash::make($password),
                'role' => 'admin',
                'phone' => env('SEED_ADMIN_PHONE', '+63 912 345 6789'),
                'address' => env('SEED_ADMIN_ADDRESS', 'Guihulngan City Hall, Guihulngan City'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        if ($this->command) {
            $this->command->info('Admin user ready: '.$email);
            $this->command->warn('Set SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD in .env to customize.');
        }
    }
}
