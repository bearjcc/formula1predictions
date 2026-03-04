<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ClairvoyantBotSeeder extends Seeder
{
    private const SEASON = 2025;

    /**
     * Create ClairvoyantBot: predicts every 2025 sprint and GP using the final
     * championship order (cheating thought-experiment bot for systems testing).
     * No DNF, preseason, midseason, or fastest lap predictions.
     */
    public function run(): void
    {
        $bot = User::firstOrCreate(
            ['email' => 'clairvoyantbot@example.com'],
            ['name' => 'ClairvoyantBot', 'password' => bcrypt('secret-password')]
        );

        $this->seedSeason($bot->id);
    }

    private function seedSeason(int $botUserId): void
    {
        $standings = Standings::getDriverStandings(self::SEASON, null);
        if ($standings->isEmpty()) {
            $this->command?->warn('No final standings for '.self::SEASON.', skipping ClairvoyantBot');

            return;
        }

        $driverOrder = $this->standingsToLocalDriverIds($standings);
        if ($driverOrder === []) {
            $this->command?->warn('No drivers resolved for '.self::SEASON.', skipping ClairvoyantBot');

            return;
        }

        $races = Races::where('season', self::SEASON)->orderBy('round')->get();
        if ($races->isEmpty()) {
            $this->command?->warn('No races for '.self::SEASON.', skipping ClairvoyantBot');

            return;
        }

        foreach ($races as $race) {
            $this->storePrediction($botUserId, $race, 'race', $driverOrder);
            if ($race->hasSprint()) {
                $this->storePrediction($botUserId, $race, 'sprint', $driverOrder);
            }
        }
    }

    /** @param  Collection<int, \App\Models\Standings>  $standings
     * @return array<int, string>
     */
    private function standingsToLocalDriverIds(Collection $standings): array
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
            $driverOrder[] = (string) $standing->entity_id;
        }

        return $driverOrder;
    }

    /** @param  array<int>  $driverOrder */
    private function storePrediction(int $userId, Races $race, string $type, array $driverOrder): void
    {
        Prediction::updateOrCreate(
            [
                'user_id' => $userId,
                'type' => $type,
                'season' => $race->season,
                'race_round' => $race->round,
            ],
            [
                'race_id' => $race->id,
                'prediction_data' => [
                    'driver_order' => $driverOrder,
                ],
                'status' => 'submitted',
            ]
        );
    }
}
