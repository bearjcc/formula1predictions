<?php

namespace App\Services;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Illuminate\Support\Arr;

class PreviousRaceResultBotService
{
    private const BOT_EMAIL = 'lastracebot@example.com';
    private const BOT_NAME = 'Last Race Result Bot';

    public function ensureBotUser(): User
    {
        return User::firstOrCreate(
            ['email' => self::BOT_EMAIL],
            ['name' => self::BOT_NAME, 'password' => bcrypt('secret-password')]
        );
    }

    public function createNextRacePrediction(Races $completedRace): ?Prediction
    {
        if (! $completedRace->isCompleted()) {
            return null;
        }

        $nextRace = Races::where('season', $completedRace->season)
            ->where('round', $completedRace->round + 1)
            ->first();

        if (! $nextRace) {
            return null;
        }

        return $this->createPredictionForTargetFromSource($completedRace, $nextRace);
    }

    public function createPredictionForTargetFromSource(Races $sourceRace, Races $targetRace): ?Prediction
    {
        $results = $sourceRace->results ?? [];

        if (empty($results)) {
            return null;
        }

        $driverApiIds = [];
        foreach ($results as $result) {
            $driverId = Arr::get($result, 'driver.driverId')
                ?? Arr::get($result, 'driver.id')
                ?? Arr::get($result, 'driver_id');

            if ($driverId) {
                $driverApiIds[] = (string) $driverId;
            }
        }

        if ($driverApiIds === []) {
            return null;
        }

        $bot = $this->ensureBotUser();

        $localDriverIds = [];
        foreach ($driverApiIds as $apiId) {
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

        $prediction = Prediction::updateOrCreate(
            [
                'user_id' => $bot->id,
                'type' => 'race',
                'season' => $targetRace->season,
                'race_round' => $targetRace->round,
            ],
            [
                'race_id' => $targetRace->id,
                'prediction_data' => [
                    'driver_order' => $localDriverIds,
                ],
            ]
        );

        $prediction->forceFill(['status' => 'submitted', 'submitted_at' => now()])->save();

        return $prediction;
    }
}

