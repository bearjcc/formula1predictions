<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Services\F1ApiService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class BotPredictionsSeeder extends Seeder
{
    public function __construct(private F1ApiService $f1) {}

    /**
     * Create a simple bot that predicts the next race as the same order as the last race.
     * Seeds for seasons 2022–2024.
     */
    public function run(): void
    {
        $bot = User::firstOrCreate(
            ['email' => 'lastracebot@example.com'],
            ['name' => 'LastRaceBot', 'password' => bcrypt('secret-password')]
        );

        foreach ([2022, 2023, 2024] as $season) {
            $this->seedSeason($bot->id, $season);
        }
    }

    private function seedSeason(int $botUserId, int $season): void
    {
        $races = $this->f1->getRacesForYear($season);

        $previousRaceOrder = null; // list of driver API ids in finish order

        foreach ($races as $race) {
            $round = $race['round'] ?? 1;

            // Determine prediction to use (previous race results)
            if ($previousRaceOrder) {
                $this->storeRacePrediction($botUserId, $season, $round, $previousRaceOrder);
            }

            // Update previous race order using current race results if available
            $results = Arr::get($race, 'results', []);
            if (! empty($results)) {
                $previousRaceOrder = array_values(array_filter(array_map(function ($r) {
                    return Arr::get($r, 'driver.id');
                }, $results)));
            }
        }
    }

    private function storeRacePrediction(int $userId, int $season, int $round, array $driverApiOrder): void
    {
        // Map API driver IDs to local Drivers ids; create placeholders if missing
        $localDriverIds = [];
        foreach ($driverApiOrder as $apiId) {
            $driver = Drivers::where('driver_id', $apiId)->first();
            if (! $driver) {
                $driver = Drivers::create([
                    'driver_id' => (string) $apiId,
                    'name' => $apiId,
                    'surname' => $apiId,
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
            $localDriverIds[] = $driver->id;
        }

        // Ensure we have a race id if races exist locally
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
                    'driver_order' => $localDriverIds,
                ],
                'status' => 'submitted',
            ]
        );
    }
}
