<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LegacyPreseason2024Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->command) {
            $this->command->info('Importing 2024 preseason predictions from XLSX export (storage/app/xlsx_export/2024/Preseason Predictions.xlsx.txt)...');
        }

        $path = storage_path('app/xlsx_export/2024/Preseason Predictions.xlsx.txt');

        if (! is_file($path) || ! is_readable($path)) {
            if ($this->command) {
                $this->command->warn("Preseason export not found or not readable: {$path}");
            }

            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || $lines === []) {
            if ($this->command) {
                $this->command->warn('Preseason export appears to be empty.');
            }

            return;
        }

        /**
         * @var array<string, string>
         */
        $cells = [];

        foreach ($lines as $line) {
            // Skip comments like "# Preseason Predictions.xlsx"
            if (str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            // Example: "B2 = McLaren"
            if (preg_match('/^([A-Z]+)(\d+)\s*=\s*(.*)$/', trim($line), $matches) === 1) {
                $column = $matches[1];
                $row = $matches[2];
                $value = trim((string) $matches[3]);
                $key = $column.$row;

                $cells[$key] = $value;
            }
        }

        // Users: Bear and Dad only (Zoe did not register in 2024)
        $users = [
            'Bear' => User::firstOrCreate(
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
        ];

        // Constructors order: rows 2–11, columns:
        //  B = Real, C = Bear, E = Dad
        $bearTeams = [];
        $dadTeams = [];
        for ($row = 2; $row <= 11; $row++) {
            $bearTeam = $cells['C'.$row] ?? '';
            $dadTeam = $cells['E'.$row] ?? '';

            $bearTeam = $this->normalizeTeamName($bearTeam);
            $dadTeam = $this->normalizeTeamName($dadTeam);

            if ($bearTeam !== null) {
                $bearTeams[] = $bearTeam;
            }

            if ($dadTeam !== null) {
                $dadTeams[] = $dadTeam;
            }
        }

        // Superlatives: rows 13–21
        //  A = label, C = Bear, E = Dad
        $bearSuperlatives = [];
        $dadSuperlatives = [];
        for ($row = 13; $row <= 21; $row++) {
            $label = $cells['A'.$row] ?? '';
            $bearValue = $cells['C'.$row] ?? '';
            $dadValue = $cells['E'.$row] ?? '';

            $label = trim($label);
            if ($label === '') {
                continue;
            }

            if ($bearValue !== '') {
                $bearSuperlatives[$label] = trim($bearValue);
            }

            if ($dadValue !== '') {
                $dadSuperlatives[$label] = trim($dadValue);
            }
        }

        $this->createPreseasonPrediction(2024, $users['Bear'], $bearTeams, [], $bearSuperlatives);
        $this->createPreseasonPrediction(2024, $users['Dad'], $dadTeams, [], $dadSuperlatives);

        if ($this->command) {
            $this->command->info('2024 preseason predictions imported successfully.');
        }
    }

    private function createPreseasonPrediction(int $year, User $user, array $teamOrder, array $driverChampionship, array $superlatives): void
    {
        // Convert team names to IDs
        $teamIds = [];
        foreach ($teamOrder as $teamName) {
            $teamName = trim($teamName);
            if ($teamName === '') {
                continue;
            }

            $team = Teams::where('team_name', 'like', "%{$teamName}%")->first();

            if ($team) {
                $teamIds[] = $team->id;
            }
        }

        // Convert driver names to IDs (not present in this sheet, but kept for symmetry)
        $driverIds = [];
        foreach ($driverChampionship as $driverName) {
            $driverName = trim($driverName);
            if ($driverName === '') {
                continue;
            }

            $driver = Drivers::where('name', 'like', "%{$driverName}%")
                ->orWhere('surname', 'like', "%{$driverName}%")
                ->first();

            if ($driver) {
                $driverIds[] = $driver->id;
            }
        }

        $prediction = Prediction::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => 'preseason',
                'season' => $year,
            ],
            [
                'prediction_data' => [
                    'team_order' => $teamIds,
                    'driver_championship' => $driverIds,
                    'superlatives' => $superlatives,
                ],
                'notes' => 'Imported from 2024 preseason XLSX export',
            ]
        );

        $prediction->forceFill([
            'status' => 'submitted',
            'submitted_at' => now(),
        ])->save();
    }

    private function normalizeTeamName(string $name): ?string
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        // Map shorthand/typos to canonical team names
        $mappings = [
            'Red Bull' => 'Red Bull Racing',
            'McLaren' => 'McLaren',
            'Ferrari' => 'Ferrari',
            'Mercedes' => 'Mercedes',
            'Aston Martin' => 'Aston Martin',
            'Alpine' => 'Alpine',
            'Haas' => 'Haas F1 Team',
            'Williams' => 'Williams',
            'William' => 'Williams',
            'Sauber' => 'Sauber',
            'RB' => 'RB',
        ];

        return $mappings[$name] ?? $name;
    }
}

