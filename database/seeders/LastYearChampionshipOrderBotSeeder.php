<?php

namespace Database\Seeders;

use App\Exceptions\F1ApiException;
use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Services\F1ApiService;
use Illuminate\Database\Seeder;

/**
 * Bot that predicts last year's driver championship order for every race of the current season.
 * Fetches order from the external F1 API. No DNFs. 2025 had 20 drivers; current season has 22
 * slots, so the bottom two entries are left blank.
 */
class LastYearChampionshipOrderBotSeeder extends Seeder
{
    /** 2025 had 20 drivers; current season grid is 22, so positions 21-22 stay blank. */
    private const PREVIOUS_YEAR_DRIVER_COUNT = 20;

    public function run(): void
    {
        $bot = User::firstOrCreate(
            ['email' => 'lastyearorderbot@example.com'],
            ['name' => 'Last Year Order Bot', 'password' => bcrypt('secret-password')]
        );

        $previousYear = config('f1.current_season') - 1;
        $f1Api = app(F1ApiService::class);

        try {
            $data = $f1Api->fetchDriversChampionship($previousYear);
        } catch (F1ApiException $e) {
            $this->command?->warn("Could not fetch {$previousYear} drivers championship: ".$e->getMessage());

            return;
        }

        $f1Api->syncDriversForSeason($previousYear);

        $entries = $data['drivers_championship'] ?? [];
        $driverOrder = [];
        $count = 0;
        foreach ($entries as $entry) {
            if ($count >= self::PREVIOUS_YEAR_DRIVER_COUNT) {
                break;
            }
            $driverId = $entry['driverId'] ?? null;
            if ($driverId === null) {
                continue;
            }
            $driver = Drivers::where('driver_id', $driverId)->first();
            if ($driver === null) {
                continue;
            }
            $driverOrder[] = $driver->id;
            $count++;
        }

        if ($driverOrder === []) {
            $this->command?->warn("No drivers resolved for {$previousYear}, skipping");

            return;
        }

        $season = config('f1.current_season');
        $races = Races::where('season', $season)->orderBy('round')->get();

        foreach ($races as $race) {
            $prediction = Prediction::updateOrCreate(
                [
                    'user_id' => $bot->id,
                    'type' => 'race',
                    'season' => $season,
                    'race_round' => $race->round,
                ],
                [
                    'race_id' => $race->id,
                    'prediction_data' => [
                        'driver_order' => $driverOrder,
                    ],
                ]
            );
            $prediction->forceFill([
                'status' => 'submitted',
                'submitted_at' => now(),
            ])->save();
        }
    }
}
