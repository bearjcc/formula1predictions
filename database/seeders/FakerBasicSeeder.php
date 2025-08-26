<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Database\Seeder;

class FakerBasicSeeder extends Seeder
{
    /**
     * Seed a small set of realistic-looking data for local development.
     */
    public function run(): void
    {
        // Users
        User::factory()->count(5)->create();

        // Teams and Drivers
        Teams::factory()->count(5)->create();
        Drivers::factory()->count(10)->create();
    }
}



