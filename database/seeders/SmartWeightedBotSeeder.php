<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SmartWeightedBotSeeder extends Seeder
{
    /**
     * Create a smart bot that predicts based on weighted driver and team performance from the last 20 races.
     * Seeds for seasons 2022–2024.
     */
    public function run(): void
    {
        $bot = User::firstOrCreate(
            ['email' => 'smartbot@example.com'],
            ['name' => 'SmartWeightedBot', 'password' => bcrypt('secret-password')]
        );

        foreach ([2022, 2023, 2024] as $season) {
            $this->seedSeason($bot->id, $season);
        }
    }

    private function seedSeason(int $botUserId, int $season): void
    {
        $races = Races::where('season', $season)->orderBy('round')->get();

        // Get last 20 races from previous seasons for weighting
        $previousRaces = $this->getLast20Races($season);

        foreach ($races as $race) {
            $driverScores = $this->calculateWeightedScores($previousRaces, $race->circuit_id);
            $predictedOrder = $this->generatePredictionOrder($driverScores);

            $this->storeRacePrediction($botUserId, $season, $race->round, $predictedOrder);
        }
    }

    private function getLast20Races(int $currentSeason): Collection
    {
        // Get races from current season and previous seasons, up to 20 total
        return Races::where('season', '<=', $currentSeason)
            ->whereNotNull('results')
            ->where('results', '!=', '[]')
            ->orderBy('season', 'desc')
            ->orderBy('round', 'desc')
            ->limit(20)
            ->get();
    }

    private function calculateWeightedScores(Collection $races, string $currentCircuitId): Collection
    {
        $driverScores = collect();

        foreach ($races as $race) {
            $results = $race->getResultsArray();
            $weight = $this->calculateRaceWeight($race, $currentCircuitId);

            foreach ($results as $position => $result) {
                $driverId = Arr::get($result, 'driver.driverId');
                if (!$driverId) continue;

                $positionScore = $this->getPositionScore($position);

                if (!$driverScores->has($driverId)) {
                    $driverScores->put($driverId, [
                        'total_score' => 0,
                        'race_count' => 0,
                        'weighted_score' => 0,
                    ]);
                }

                $driverData = $driverScores->get($driverId);
                $driverData['total_score'] += $positionScore;
                $driverData['race_count'] += 1;
                $driverData['weighted_score'] += ($positionScore * $weight);

                $driverScores->put($driverId, $driverData);
            }
        }

        // Calculate final weighted averages
        return $driverScores->map(function ($data) {
            $data['average_score'] = $data['race_count'] > 0 ? $data['total_score'] / $data['race_count'] : 0;
            return $data;
        })->sortByDesc('weighted_score');
    }

    private function calculateRaceWeight(Races $race, string $currentCircuitId): float
    {
        $baseWeight = 1.0;

        // Increase weight for recent races
        $recencyWeight = min(1.0, $race->season / 2024); // More recent seasons get higher weight

        // Increase weight for same circuit
        if ($race->circuit_id === $currentCircuitId) {
            $recencyWeight *= 2.0; // Double weight for same circuit
        }

        // Increase weight for similar circuits (could be enhanced with circuit similarity logic)
        $circuitSimilarityWeight = $this->getCircuitSimilarityWeight($race->circuit_id, $currentCircuitId);

        return $baseWeight * $recencyWeight * $circuitSimilarityWeight;
    }

    private function getCircuitSimilarityWeight(string $raceCircuitId, string $currentCircuitId): float
    {
        // For now, just return 1.0 for all circuits
        // This could be enhanced to compare circuit characteristics
        return 1.0;
    }

    private function getPositionScore(int $position): int
    {
        // Higher positions (lower numbers) get higher scores
        return match ($position) {
            0 => 25, // 1st place
            1 => 18, // 2nd place
            2 => 15, // 3rd place
            3 => 12, // 4th place
            4 => 10, // 5th place
            5 => 8,  // 6th place
            6 => 6,  // 7th place
            7 => 4,  // 8th place
            8 => 2,  // 9th place
            9 => 1,  // 10th place
            default => 0, // Outside top 10
        };
    }

    private function generatePredictionOrder(Collection $driverScores): array
    {
        // Sort drivers by their weighted scores (highest first)
        return $driverScores->sortByDesc('weighted_score')->keys()->toArray();
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

