<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ChampionshipOrderBotSeeder extends Seeder
{
    /** @var array<int> */
    private const SEASONS = [2022, 2023, 2024, 2025];

    /**
     * Create SeasonBot: predicts each race using current championship order.
     * Round 1 uses previous year's final standings; round N uses standings after round N-1.
     */
    public function run(): void
    {
        $bot = User::firstOrCreate(
            ['email' => 'seasonbot@example.com'],
            ['name' => 'SeasonBot', 'password' => bcrypt('secret-password')]
        );

        foreach (self::SEASONS as $season) {
            $this->seedSeason($bot->id, $season);
        }
    }

    private function seedSeason(int $botUserId, int $season): void
    {
        $races = Races::where('season', $season)->orderBy('round')->get();

        if ($races->isEmpty()) {
            $this->command?->warn("No races for season {$season}, skipping ChampionshipOrderBot");

            return;
        }

        foreach ($races as $race) {
            $standings = $this->getStandingsBeforeRound($season, $race->round);
            if ($standings->isEmpty()) {
                $this->command?->warn("No standings before round {$race->round} for {$season}, skipping");

                continue;
            }
            $this->storeRacePrediction($botUserId, $season, $race->round, $standings);
        }
    }

    /**
     * Standings to use for predicting this round: after previous round, or previous year final for round 1.
     */
    private function getStandingsBeforeRound(int $season, int $round): Collection
    {
        if ($round <= 1) {
            return Standings::getDriverStandings($season - 1, null);
        }

        return Standings::getDriverStandings($season, $round - 1);
    }

    /** @param  Collection<int, \App\Models\Standings>  $standings */
    private function storeRacePrediction(int $userId, int $season, int $round, Collection $standings): void
    {
        $driverOrder = [];
        foreach ($standings as $standing) {
            $driver = Drivers::where('driver_id', $standing->entity_id)->first();
            if (! $driver) {
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
