<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PreviousCircuitBotSeeder extends Seeder
{
    /**
     * Create a bot that predicts the next race based on the results of the last race at the same circuit.
     * Seeds for seasons 2022–2024.
     */
    public function run(): void
    {
        $bot = User::firstOrCreate(
            ['email' => 'circuitbot@example.com'],
            ['name' => 'CircuitBot', 'password' => bcrypt('secret-password')]
        );

        foreach ([2022, 2023, 2024] as $season) {
            $this->seedSeason($bot->id, $season);
        }
    }

    private function seedSeason(int $botUserId, int $season): void
    {
        $races = Races::where('season', $season)->orderBy('round')->get();

        foreach ($races as $race) {
            // Find the last race at this circuit from previous years
            $previousRaceAtCircuit = $this->findLastRaceAtCircuit($race->circuit_id, $season);

            if ($previousRaceAtCircuit && !empty($previousRaceAtCircuit['results'])) {
                $previousResults = Arr::get($previousRaceAtCircuit, 'results', []);
                $driverOrder = $this->extractDriverOrder($previousResults);

                $this->storeRacePrediction($botUserId, $season, $race->round, $driverOrder);
            }
        }
    }

    private function findLastRaceAtCircuit(string $circuitId, int $currentSeason): ?array
    {
        // Look for races at this circuit in previous seasons (most recent first)
        $previousRaces = Races::where('circuit_id', $circuitId)
            ->where('season', '<', $currentSeason)
            ->orderBy('season', 'desc')
            ->orderBy('round', 'desc')
            ->limit(5) // Check last 5 races at this circuit
            ->get();

        foreach ($previousRaces as $race) {
            $raceData = $race->toArray();
            if (!empty($raceData['results'])) {
                return $raceData;
            }
        }

        return null;
    }

    private function extractDriverOrder(array $results): array
    {
        $driverOrder = [];

        foreach ($results as $result) {
            $driverId = Arr::get($result, 'driver.id');
            if ($driverId) {
                $driverOrder[] = $driverId;
            }
        }

        return $driverOrder;
    }

    private function storeRacePrediction(int $userId, int $season, int $round, array $driverOrder): void
    {
        // Map API driver IDs to local Drivers ids; create placeholders if missing
        $localDriverIds = [];
        foreach ($driverOrder as $apiId) {
            $driver = Drivers::where('driver_id', $apiId)->first();

            if (!$driver) {
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
                    'driver_order' => $localDriverIds,
                ],
                'status' => 'submitted',
            ]
        );
    }
}

