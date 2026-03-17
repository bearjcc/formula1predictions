<?php

namespace App\Console\Commands;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EnsureZoeRound2Prediction extends Command
{
    protected $signature = 'app:ensure-zoe-round2-prediction-once';

    protected $description = 'Backfill Zoe Pasternack\'s round 2 race prediction once per environment.';

    private const JOB_NAME = 'zoe_round2_prediction_2026_r2';

    public function handle(): int
    {
        if (! Schema::hasTable('one_time_jobs')) {
            $this->warn('one_time_jobs table missing. Run migrations first.');

            return Command::SUCCESS;
        }

        if (DB::table('one_time_jobs')->where('name', self::JOB_NAME)->exists()) {
            $this->info('Zoe round 2 prediction already ensured. Skipping.');

            return Command::SUCCESS;
        }

        $user = User::where('email', 'zoepasternack@gmail.com')->first();

        if (! $user) {
            $this->warn('User zoepasternack@gmail.com not found. Nothing to do.');
            Log::warning('EnsureZoeRound2Prediction: user not found', ['email' => 'zoepasternack@gmail.com']);

            $this->markJobCompleted();

            return Command::SUCCESS;
        }

        $season = (int) config('f1.current_season');
        $race = Races::where('season', $season)->where('round', 2)->first();

        if (! $race) {
            $this->warn("Round 2 race for season {$season} not found. Nothing to do.");
            Log::warning('EnsureZoeRound2Prediction: race not found', ['season' => $season, 'round' => 2]);

            $this->markJobCompleted();

            return Command::SUCCESS;
        }

        $prediction = Prediction::where('user_id', $user->id)
            ->where('type', 'race')
            ->where('season', $season)
            ->where('race_round', 2)
            ->first();

        if ($prediction) {
            $this->info('Zoe already has a round 2 race prediction. Ensuring it is linked and scored if possible.');
        } else {
            $driverOrder = $this->buildDriverOrder();

            if ($driverOrder === []) {
                $this->warn('Could not resolve any drivers for Zoe\'s prediction. Skipping creation.');
                Log::warning('EnsureZoeRound2Prediction: empty driver order after resolution');

                $this->markJobCompleted();

                return Command::SUCCESS;
            }

            $prediction = new Prediction();
            $prediction->fill([
                'user_id' => $user->id,
                'type' => 'race',
                'season' => $season,
                'race_round' => (int) $race->round,
                'race_id' => $race->id,
                'prediction_data' => [
                    'driver_order' => $driverOrder,
                ],
            ]);
            $prediction->status = 'submitted';
            $prediction->submitted_at = now();
            $prediction->save();

            $this->info('Created Zoe\'s round 2 race prediction from emailed picks.');
        }

        if (! $prediction->race_id) {
            $prediction->race_id = $race->id;
            $prediction->save();
        }

        if ($race->isCompleted() && $race->getResultsArray() !== []) {
            $this->info('Race has results; scoring Zoe\'s prediction now.');

            $prediction->refresh();
            $prediction->score();
        } else {
            $this->info('Race results not available yet; prediction will be scored by normal background jobs later.');
        }

        $this->markJobCompleted();

        return Command::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function buildDriverOrder(): array
    {
        $names = [
            'George Russell',
            'Andrea Kimi Antonelli',
            'Charles Leclerc',
            'Lando Norris',
            'Max Verstappen',
            'Lewis Hamilton',
            'Oscar Piastri',
            'Pierre Gasly',
            'Oliver Bearman',
            'Esteban Ocon',
            'Liam Lawson',
            'Arvid Lindblad',
            'Valtteri Bottas',
            'Alex Albon',
            'Sergio Pérez',
            'Carlos Sainz',
            'Gabriel Bortoleto',
            'Isack Hadjar',
            'Nico Hulkenberg',
            'Franco Colapinto',
            'Lance Stroll',
            'Fernando Alonso',
        ];

        $ids = [];

        foreach ($names as $fullName) {
            $driver = $this->findDriverByFullName($fullName);

            if (! $driver && $fullName === 'Sergio Pérez') {
                $driver = $this->findDriverByFullName('Sergio Perez');
            }

            if (! $driver) {
                $this->warn("Driver not found for full name: {$fullName}");
                Log::warning('EnsureZoeRound2Prediction: driver not found', ['full_name' => $fullName]);

                continue;
            }

            $ids[] = $driver->driver_id ?: (string) $driver->id;
        }

        return $ids;
    }

    private function findDriverByFullName(string $fullName): ?Drivers
    {
        $normalized = $this->normalizeName($fullName);

        $drivers = Drivers::with('team')->get();

        foreach ($drivers as $driver) {
            $candidate = trim((string) $driver->name.' '.(string) $driver->surname);
            if ($candidate === '') {
                continue;
            }

            if ($this->normalizeName($candidate) === $normalized) {
                return $driver;
            }
        }

        return null;
    }

    private function normalizeName(string $name): string
    {
        $name = trim($name);
        $name = mb_strtolower($name);

        $replacements = [
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'á' => 'a',
            'à' => 'a',
            'ä' => 'a',
            'ö' => 'o',
            'ü' => 'u',
            'ñ' => 'n',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ç' => 'c',
        ];

        return strtr($name, $replacements);
    }

    private function markJobCompleted(): void
    {
        DB::table('one_time_jobs')->insert([
            'name' => self::JOB_NAME,
            'run_at' => now(),
        ]);
    }
}

