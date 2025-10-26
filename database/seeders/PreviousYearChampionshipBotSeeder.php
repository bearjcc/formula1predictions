<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\User;
use Illuminate\Database\Seeder;

class PreviousYearChampionshipBotSeeder extends Seeder
{
    /**
     * Create a bot that predicts the next race based on the previous year's championship standings.
     * Seeds for seasons 2022–2024.
     */
    public function run(): void
    {
        $bot = User::firstOrCreate(
            ['email' => 'championshipbot@example.com'],
            ['name' => 'ChampionshipBot', 'password' => bcrypt('secret-password')]
        );

        foreach ([2023, 2024] as $season) {
            $this->seedSeason($bot->id, $season);
        }
    }

    private function seedSeason(int $botUserId, int $season): void
    {
        // Get previous year's final driver standings
        $previousYear = $season - 1;
        $previousStandings = Standings::getDriverStandings($previousYear);

        if ($previousStandings->isEmpty()) {
            $this->command->warn("No standings found for {$previousYear}, skipping season {$season}");
            return;
        }

        // Get races for current season
        $races = Races::where('season', $season)->orderBy('round')->get();

        foreach ($races as $race) {
            $this->storeRacePrediction($botUserId, $season, $race->round, $previousStandings);
        }
    }

    private function storeRacePrediction(int $userId, int $season, int $round, $previousStandings): void
    {
        // Map standings to driver order (championship position determines prediction order)
        $driverOrder = [];
        foreach ($previousStandings as $standing) {
            $driver = Drivers::where('driver_id', $standing->entity_id)->first();

            if (!$driver) {
                // Create placeholder driver if not found
                $driver = Drivers::create([
                    'driver_id' => (string) $standing->entity_id,
                    'name' => $standing->entity_name,
                    'surname' => $standing->entity_name,
                    'nationality' => 'Unknown',
                    'url' => null,
                    'driver_number' => null,
                    'description' => null,
                    'photo_url' => null,
                    'helmet_url' => null,
                    'date_of_birth' => null,
                    'website' => null,
                    'twitter' => null,
                    'instagram' => null,
                    'world_championships' => 0,
                    'race_wins' => 0,
                    'podiums' => 0,
                    'pole_positions' => 0,
                    'fastest_laps' => 0,
                    'points' => 0,
                    'is_active' => true,
                ]);
            }

            $driverOrder[] = $driver->id;
        }

        // Ensure we have a race record
        $race = Races::where('season', $season)->where('round', $round)->first();

        Prediction::updateOrCreate(
            [
                'user_id' => $userId,
                'type' => 'race',
                'season' => $season,
                'race_round' => $round,
            ],
            [
                'race_id' => $race?->id,
                'prediction_data' => [
                    'driver_order' => $driverOrder,
                ],
                'status' => 'submitted',
            ]
        );
    }
}

