<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LegacyCsvPredictions2023Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->command) {
            $this->command->info('Importing 2023 predictions from CSV (storage/app/xlsx_export/predictions_2023.csv)...');
        }

        $path = storage_path('app/xlsx_export/predictions_2023.csv');

        if (! is_file($path) || ! is_readable($path)) {
            if ($this->command) {
                $this->command->warn("CSV not found or not readable: {$path}");
            }

            return;
        }

        // Create or reuse users for CSV-based historical data
        $users = [
            'Joseph' => User::firstOrCreate(
                ['email' => 'bearjcc@example.com'],
                [
                    'name' => 'Bear JCC',
                    'password' => Hash::make('password'),
                ],
            ),
            'Dad' => User::firstOrCreate(
                ['email' => 'ccaswell@example.com'],
                [
                    'name' => 'CCaswell',
                    'password' => Hash::make('password'),
                ],
            ),
            'Zoe' => User::firstOrCreate(
                ['email' => 'sunny@example.com'],
                [
                    'name' => 'Sunny',
                    'password' => Hash::make('password'),
                ],
            ),
            'ChatGPT' => User::firstOrCreate(
                ['email' => 'chatgpt@example.com'],
                [
                    'name' => 'ChatGPT',
                    'password' => Hash::make('password'),
                ],
            ),
        ];

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            if ($this->command) {
                $this->command->error("Unable to open CSV file for reading: {$path}");
            }

            return;
        }

        try {
            $header = fgetcsv($handle);
            if (! is_array($header)) {
                if ($this->command) {
                    $this->command->warn('CSV appears to be empty or missing header row.');
                }

                return;
            }

            $columns = array_flip($header);

            $requiredColumns = ['year', 'race', 'predictor', 'position', 'driver_name'];
            foreach ($requiredColumns as $column) {
                if (! array_key_exists($column, $columns)) {
                    if ($this->command) {
                        $this->command->error("Missing required column '{$column}' in CSV header.");
                    }

                    return;
                }
            }

            /**
             * @var array<string, array<string, array<int, string>>>
             *                     predictor => race => [position => driverName]
             */
            $grouped = [];

            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null] || $row === [] || (count($row) === 1 && trim((string) $row[0]) === '')) {
                    continue;
                }

                $year = (int) ($row[$columns['year']] ?? 0);
                if ($year !== 2023) {
                    continue;
                }

                $predictor = (string) ($row[$columns['predictor']] ?? '');
                if ($predictor === '' || ! isset($users[$predictor])) {
                    continue;
                }

                $raceName = trim((string) ($row[$columns['race']] ?? ''));
                $position = (int) ($row[$columns['position']] ?? 0);
                $driverRaw = (string) ($row[$columns['driver_name']] ?? '');
                $driverName = $this->normalizeDriverName($driverRaw);

                if ($raceName === '' || $position <= 0 || $driverName === null) {
                    continue;
                }

                if (! isset($grouped[$predictor][$raceName])) {
                    $grouped[$predictor][$raceName] = [];
                }

                $grouped[$predictor][$raceName][$position] = $driverName;
            }
        } finally {
            fclose($handle);
        }

        foreach ($grouped as $predictor => $races) {
            $user = $users[$predictor];

            foreach ($races as $raceName => $positions) {
                ksort($positions);
                $driverOrder = array_values($positions);

                $this->createRacePrediction($user, 2023, $raceName, $driverOrder);
            }
        }

        if ($this->command) {
            $this->command->info('2023 CSV predictions imported successfully.');
        }
    }

    private function createRacePrediction(User $user, int $year, string $raceName, array $driverOrder): void
    {
        $race = Races::firstOrCreate([
            'season' => $year,
            'race_name' => $raceName,
        ], [
            'round' => $this->getRaceRound($raceName),
            'date' => now(),
            'status' => 'completed',
        ]);

        $driverIds = [];
        foreach ($driverOrder as $driverName) {
            $driver = Drivers::where('name', 'like', "%{$driverName}%")
                ->orWhere('surname', 'like', "%{$driverName}%")
                ->first();

            if ($driver) {
                $driverIds[] = $driver->driver_id ?? (string) $driver->id;
            }
        }

        $prediction = Prediction::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => 'race',
                'season' => $year,
                'race_round' => $race->round,
            ],
            [
                'race_id' => $race->id,
                'prediction_data' => [
                    'driver_order' => $driverIds,
                    'fastest_lap' => null,
                ],
                'notes' => "Imported from CSV historical data - {$raceName}",
            ]
        );

        $prediction->forceFill([
            'status' => 'submitted',
            'submitted_at' => now(),
        ])->save();
    }

    private function normalizeDriverName(string $name): ?string
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        $mappings = [
            'Logan Sergant' => 'Logan Sargeant',
            'Nyck De Vries' => 'Nyck de Vries',
            'Max Verstappen' => 'Max Verstappen',
            'Sergio Perez' => 'Sergio Perez',
            'Charles Leclerc' => 'Charles Leclerc',
            'Carlos Sainz' => 'Carlos Sainz',
            'Lewis Hamilton' => 'Lewis Hamilton',
            'George Russell' => 'George Russell',
            'Fernando Alonso' => 'Fernando Alonso',
            'Lance Stroll' => 'Lance Stroll',
            'Lando Norris' => 'Lando Norris',
            'Oscar Piastri' => 'Oscar Piastri',
            'Valtteri Bottas' => 'Valtteri Bottas',
            'Pierre Gasly' => 'Pierre Gasly',
            'Esteban Ocon' => 'Esteban Ocon',
            'Kevin Magnussen' => 'Kevin Magnussen',
            'Nico Hulkenberg' => 'Nico Hulkenberg',
            'Alex Albon' => 'Alex Albon',
            'Yuki Tsunoda' => 'Yuki Tsunoda',
            'Daniel Ricciardo' => 'Daniel Ricciardo',
        ];

        return $mappings[$name] ?? $name;
    }

    private function getRaceRound(string $raceName): int
    {
        $raceRounds = [
            'Bahrain' => 1,
            'Saudi Arabia' => 2,
            'Australia' => 3,
            'Emilia Romagna' => 4,
            'Miami' => 5,
            'Spain' => 6,
            'Monaco' => 7,
            'Azerbaijan' => 8,
            'Canada' => 9,
            'Austria' => 10,
            'Great Britain' => 11,
            'Hungary' => 12,
            'Belgium' => 13,
            'Netherlands' => 14,
            'Italy' => 15,
            'Singapore' => 16,
            'Japan' => 17,
            'Qatar' => 18,
            'United States' => 19,
            'Mexico' => 20,
            'Brazil' => 21,
            'Las Vegas' => 22,
            'Abu Dhabi' => 23,
        ];

        return $raceRounds[$raceName] ?? 1;
    }
}

