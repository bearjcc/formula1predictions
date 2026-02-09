<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Seed test users for manual and automated testing.
     * Covers: verified normal, admin, unverified.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => $password,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => $password,
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'unverified@example.com'],
            [
                'name' => 'Unverified User',
                'password' => $password,
                'email_verified_at' => null,
            ]
        );
    }
}
