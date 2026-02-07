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
        $this->call(TestUserSeeder::class);

        // Call other seeders
        $this->call([
            HistoricalPredictionsSeeder::class,
            // FakerBasicSeeder::class,
            // BotPredictionsSeeder::class,
        ]);
    }
}
