<?php

namespace App\Console\Commands;

use App\Models\Races;
use App\Services\PreviousRaceResultBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsurePreviousRaceBotInitialPrediction extends Command
{
    protected $signature = 'app:ensure-previous-race-bot-initial-prediction';

    protected $description = 'Seed previous-race bot prediction for 2026 round 1 from the final 2025 race once per environment.';

    private const JOB_NAME = 'previous_race_bot_2026_r1';

    public function handle(): int
    {
        if (! Schema::hasTable('one_time_jobs')) {
            $this->warn('one_time_jobs table missing. Run migrations first.');

            return Command::SUCCESS;
        }

        $ran = DB::table('one_time_jobs')->where('name', self::JOB_NAME)->exists();
        if ($ran) {
            $this->info('Previous-race bot initial prediction already seeded. Skipping.');

            return Command::SUCCESS;
        }

        $sourceRace = Races::where('season', 2025)
            ->orderByDesc('round')
            ->first();
        $targetRace = Races::where('season', 2026)
            ->orderBy('round')
            ->first();

        if (! $sourceRace || ! $targetRace) {
            $this->warn('Required races for 2025 or 2026 not found. Ensure season data is synced first.');

            return Command::SUCCESS;
        }

        $this->info("Seeding previous-race bot prediction for 2026 round {$targetRace->round} from 2025 round {$sourceRace->round}...");

        /** @var PreviousRaceResultBotService $service */
        $service = app(PreviousRaceResultBotService::class);
        $prediction = $service->createPredictionForTargetFromSource($sourceRace, $targetRace);

        if (! $prediction) {
            $this->warn('Could not create previous-race bot prediction (missing results or drivers).');

            return Command::SUCCESS;
        }

        DB::table('one_time_jobs')->insert([
            'name' => self::JOB_NAME,
            'run_at' => now(),
        ]);

        $this->info('Previous-race bot initial prediction seeded. Flag set so this will not run again.');

        return Command::SUCCESS;
    }
}

