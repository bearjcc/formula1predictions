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
     * Create LastBot: predicts each race as the same order as the last race.
     * First race of the year uses the last race of the previous year.
     * Seeds for seasons 2022–2024.
     */
    public function run(): void
    {
        $bot = User::firstOrCreate(
            ['email' => 'lastbot@example.com'],
            ['name' => 'LastBot', 'password' => bcrypt('secret-password')]
        );

        foreach ([2022, 2023, 2024] as $season) {
            $this->seedSeason($bot->id, $season);
        }
    }

    private function seedSeason(int $botUserId, int $season): void
    {
        $races = $this->f1->getRacesForYear($season);

        // First race of year: use last race of previous year
        $previousRaceOrder = $this->getLastRaceDriverOrder($season - 1);

        foreach ($races as $race) {
            $round = (int) ($race['round'] ?? 1);

            if (! empty($previousRaceOrder)) {
                $this->storeRacePrediction($botUserId, $season, $round, $previousRaceOrder);
            }

            $results = Arr::get($race, 'results', []);
            if (! empty($results)) {
                $previousRaceOrder = $this->driverIdsFromResults($results);
            }
        }
    }

    /**
     * Get driver API IDs in finish order from the last race of the given season.
     *
     * @return array<int, string>
     */
    private function getLastRaceDriverOrder(int $season): array
    {
        $races = $this->f1->getRacesForYear($season);
        if (empty($races)) {
            return [];
        }
        $last = end($races);
        $results = Arr::get($last, 'results', []);

        return $this->driverIdsFromResults($results);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     * @return array<int, string>
     */
    private function driverIdsFromResults(array $results): array
    {
        return array_values(array_filter(array_map(function ($r) {
            return Arr::get($r, 'driver.id') ?? Arr::get($r, 'driverId') ?? Arr::get($r, 'driver_id');
        }, $results)));
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

        $prediction = Prediction::updateOrCreate(
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
            ]
        );
        $prediction->forceFill(['status' => 'submitted', 'submitted_at' => now()])->save();
    }
}
