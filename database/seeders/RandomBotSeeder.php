<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\User;
use Illuminate\Database\Seeder;

class RandomBotSeeder extends Seeder
{
    /** @var array<int> */
    private const SEASONS = [2022, 2023, 2024, 2025];

    /**
     * Create a bot that predicts a random driver order for each race.
     * Uses championship standings (current or previous year) to get driver list; shuffles for each race.
     */
    public function run(): void
    {
        $bot = User::firstOrCreate(
            ['email' => 'randombot@example.com'],
            ['name' => 'RandomBot', 'password' => bcrypt('secret-password')]
        );

        foreach (self::SEASONS as $season) {
            $this->seedSeason($bot->id, $season);
        }
    }

    private function seedSeason(int $botUserId, int $season): void
    {
        $races = Races::where('season', $season)->orderBy('round')->get();
        if ($races->isEmpty()) {
            $this->command?->warn("No races for season {$season}, skipping RandomBot");

            return;
        }

        $driverIds = $this->getDriverIdsForSeason($season);
        if ($driverIds === []) {
            $this->command?->warn("No drivers for season {$season}, skipping RandomBot");

            return;
        }

        foreach ($races as $race) {
            $shuffled = $driverIds;
            shuffle($shuffled);
            $this->storeRacePrediction($botUserId, $season, $race->round, $shuffled);
        }
    }

    /**
     * Get canonical driver IDs for the season (from standings or all active drivers).
     *
     * @return array<int, string>
     */
    private function getDriverIdsForSeason(int $season): array
    {
        $standings = Standings::getDriverStandings($season, null);
        if ($standings->isEmpty()) {
            $standings = Standings::getDriverStandings($season - 1, null);
        }
        if ($standings->isEmpty()) {
            return Drivers::where('is_active', true)
                ->orderBy('id')
                ->limit(22)
                ->pluck('driver_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();
        }

        $ids = [];
        foreach ($standings as $standing) {
            $driver = Drivers::where('driver_id', $standing->entity_id)->first();
            if ($driver) {
                $ids[] = (string) $driver->driver_id;
            }
        }

        return $ids;
    }

    /** @param  array<int, string>  $driverIds */
    private function storeRacePrediction(int $userId, int $season, int $round, array $driverIds): void
    {
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
                    'driver_order' => array_values(array_map('strval', $driverIds)),
                ],
                'status' => 'submitted',
            ]
        );
    }
}
