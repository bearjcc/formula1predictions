<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prediction;
use App\Models\Drivers;
use App\Models\Teams;
use App\Models\Races;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HistoricalPredictionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->command) {
            $this->command->info('Importing historical F1 predictions...');
        }

        // Create test users for historical data
        $users = [
            'bearjcc' => User::factory()->create([
                'name' => 'Bear JCC',
                'email' => 'bearjcc@example.com',
            ]),
            'sunny' => User::factory()->create([
                'name' => 'Sunny',
                'email' => 'sunny@example.com',
            ]),
            'ccaswell' => User::factory()->create([
                'name' => 'CCaswell',
                'email' => 'ccaswell@example.com',
            ]),
            'chatgpt' => User::factory()->create([
                'name' => 'ChatGPT',
                'email' => 'chatgpt@example.com',
            ]),
        ];

        // Import predictions for each year and user
        $this->importPredictionsForYear(2022, $users);
        $this->importPredictionsForYear(2023, $users);

        if ($this->command) {
            $this->command->info('Historical predictions imported successfully!');
        }
    }

    private function importPredictionsForYear(int $year, array $users): void
    {
        if ($this->command) {
            $this->command->info("Importing predictions for {$year}...");
        }

        foreach ($users as $username => $user) {
            $filename = "previous/predictions.{$year}.{$username}.md";
            
            if (!File::exists($filename)) {
                if ($this->command) {
                    $this->command->warn("File not found: {$filename}");
                }
                continue;
            }

            if ($this->command) {
                $this->command->info("Processing {$filename}...");
            }
            $this->importUserPredictions($filename, $user, $year);
        }
    }

    private function importUserPredictions(string $filename, User $user, int $year): void
    {
        $content = File::get($filename);
        $lines = explode("\n", $content);

        $currentRace = null;
        $currentSection = null;
        $driverOrder = [];
        $fastestLap = null;
        $teamOrder = [];
        $driverChampionship = [];
        $superlatives = [];

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }

            // Check for race headers
            if (preg_match('/^## ([A-Za-z\s]+)$/', $line, $matches)) {
                $raceName = trim($matches[1]);
                
                // Save previous race prediction if exists
                if ($currentRace && !empty($driverOrder)) {
                    $this->createRacePrediction($user, $year, $raceName, $driverOrder, $fastestLap);
                }

                // Reset for new race
                $currentRace = $raceName;
                $driverOrder = [];
                $fastestLap = null;
                continue;
            }

            // Check for fastest lap
            if (preg_match('/^FL -> (.+)$/', $line, $matches)) {
                $fastestLap = trim($matches[1]);
                if ($fastestLap === 'null') {
                    $fastestLap = null;
                }
                continue;
            }

            // Check for section headers
            if (preg_match('/^### (.+)$/', $line, $matches)) {
                $currentSection = strtolower(trim($matches[1]));
                continue;
            }

            // Process driver names (for race predictions)
            if ($currentRace && !in_array($currentSection, ['team championship order', 'superlatives', 'drivers', 'teammates', 'driver championship', 'teams', 'predictions'])) {
                $driverName = $this->normalizeDriverName($line);
                if ($driverName) {
                    $driverOrder[] = $driverName;
                }
            }

            // Process preseason sections
            if ($currentSection === 'team championship order') {
                $teamName = $this->normalizeTeamName($line);
                if ($teamName) {
                    $teamOrder[] = $teamName;
                }
            }

            if ($currentSection === 'drivers') {
                $driverName = $this->normalizeDriverName($line);
                if ($driverName) {
                    $driverChampionship[] = $driverName;
                }
            }

            if ($currentSection === 'superlatives') {
                if (strpos($line, "\t") !== false) {
                    $parts = explode("\t", $line);
                    if (count($parts) === 2) {
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        $superlatives[$key] = $value;
                    }
                }
            }
        }

        // Save final race prediction
        if ($currentRace && !empty($driverOrder)) {
            $this->createRacePrediction($user, $year, $currentRace, $driverOrder, $fastestLap);
        }

        // Save preseason prediction if we have data
        if (!empty($teamOrder) || !empty($driverChampionship)) {
            $this->createPreseasonPrediction($user, $year, $teamOrder, $driverChampionship, $superlatives);
        }
    }

    private function createRacePrediction(User $user, int $year, string $raceName, array $driverOrder, ?string $fastestLap): void
    {
        // Find or create the race
        $race = Races::firstOrCreate([
            'season' => $year,
            'race_name' => $raceName,
        ], [
            'round' => $this->getRaceRound($raceName), // Get proper round number
            'date' => now(),
            'status' => 'completed',
        ]);

        // Convert driver names to IDs
        $driverIds = [];
        foreach ($driverOrder as $driverName) {
            $driver = Drivers::where('name', 'like', "%{$driverName}%")
                ->orWhere('surname', 'like', "%{$driverName}%")
                ->first();
            
            if ($driver) {
                $driverIds[] = $driver->id;
            }
        }

        // Find fastest lap driver ID
        $fastestLapId = null;
        if ($fastestLap) {
            $fastestLapDriver = Drivers::where('name', 'like', "%{$fastestLap}%")
                ->orWhere('surname', 'like', "%{$fastestLap}%")
                ->first();
            
            if ($fastestLapDriver) {
                $fastestLapId = $fastestLapDriver->id;
            }
        }

        // Upsert to avoid unique constraint collisions when re-running
        Prediction::updateOrCreate(
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
                    'fastest_lap' => $fastestLapId,
                ],
                'status' => 'submitted',
                'submitted_at' => now(),
                'notes' => "Imported from historical data - {$raceName}",
            ]
        );
    }

    private function createPreseasonPrediction(User $user, int $year, array $teamOrder, array $driverChampionship, array $superlatives): void
    {
        // Convert team names to IDs
        $teamIds = [];
        foreach ($teamOrder as $teamName) {
            $team = Teams::where('team_name', 'like', "%{$teamName}%")
                ->first();
            
            if ($team) {
                $teamIds[] = $team->id;
            }
        }

        // Convert driver names to IDs
        $driverIds = [];
        foreach ($driverChampionship as $driverName) {
            $driver = Drivers::where('name', 'like', "%{$driverName}%")
                ->orWhere('surname', 'like', "%{$driverName}%")
                ->first();
            
            if ($driver) {
                $driverIds[] = $driver->id;
            }
        }

        // Upsert preseason prediction to avoid duplicates
        Prediction::updateOrCreate(
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
                'status' => 'submitted',
                'submitted_at' => now(),
                'notes' => "Imported from historical data - Preseason predictions",
            ]
        );
    }

    private function normalizeDriverName(string $name): ?string
    {
        $name = trim($name);
        
        // Skip empty lines and headers
        if (empty($name) || strpos($name, '#') === 0 || strpos($name, 'FL ->') === 0) {
            return null;
        }

        // Common name mappings
        $mappings = [
            'Logan Sergant' => 'Logan Sargeant',
            'Nyck De Vries' => 'Nyck de Vries',
            'Zhou Guanyu' => 'Zhou Guanyu',
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
            'Zhou Guanyu' => 'Zhou Guanyu',
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

    private function normalizeTeamName(string $name): ?string
    {
        $name = trim($name);
        
        // Skip empty lines and headers
        if (empty($name) || strpos($name, '#') === 0) {
            return null;
        }

        // Common team name mappings
        $mappings = [
            'Red Bull' => 'Red Bull Racing',
            'Mercedes' => 'Mercedes',
            'Ferrari' => 'Ferrari',
            'Aston Martin' => 'Aston Martin',
            'Alfa Romeo' => 'Alfa Romeo',
            'McLaren' => 'McLaren',
            'Alpine' => 'Alpine',
            'Haas' => 'Haas F1 Team',
            'Williams' => 'Williams',
            'AlphaTauri' => 'AlphaTauri',
        ];

        return $mappings[$name] ?? $name;
    }

    private function getRaceRound(string $raceName): int
    {
        // Map race names to their typical round numbers
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

        return $raceRounds[$raceName] ?? 1; // Default to round 1 if not found
    }
}
