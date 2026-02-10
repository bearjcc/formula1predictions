<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the admin user from environment variables.
     * Run this seeder after deployment to create the first admin.
     *
     * Environment variables:
     * - ADMIN_EMAIL: Email address for the admin user (required)
     * - ADMIN_PASSWORD: Password for the admin user (optional, defaults to 'password')
     * - ADMIN_NAME: Display name for the admin user (optional, defaults to 'Admin')
     *
     * Usage:
     * php artisan db:seed --class=AdminSeeder
     */
    public function run(): void
    {
        $email = config('admin.promotable_admin_email');
        $password = config('admin.admin_password') ?? 'password';
        $name = config('admin.admin_name') ?? 'Admin';

        if (empty($email)) {
            if ($this->command) {
                $this->command->warn('ADMIN_EMAIL not set in .env. Skipping admin seeder.');
                $this->command->comment('Set ADMIN_EMAIL in .env and run: php artisan db:seed --class=AdminSeeder');
            }

            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );
        $user->forceFill(['is_admin' => true])->save();

        if ($this->command) {
            if ($user->wasRecentlyCreated) {
                $this->command->info("Created admin user: {$email}");
            } else {
                $this->command->info("Updated admin user: {$email}");
            }

            $this->command->warn("Login with email: {$email}");
            $this->command->warn('Change your password after first login!');
        }
    }
}
