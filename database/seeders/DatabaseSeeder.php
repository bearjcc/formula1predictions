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
        // Create admin user from environment variables (if ADMIN_EMAIL is set)
        $this->call(AdminSeeder::class);

        $this->call(TestUserSeeder::class);

        // Call other seeders
        $this->call([
            HistoricalPredictionsSeeder::class,
            // FakerBasicSeeder::class,
            // BotPredictionsSeeder::class,
        ]);
    }
}
