<?php

namespace App\Console\Commands;

use Database\Seeders\LastYearChampionshipOrderBotSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureTestYearBotPredictions extends Command
{
    protected $signature = 'app:ensure-test-year-bot-predictions';

    protected $description = 'Seed Last Year Order Bot predictions for 2025 and 2026 once per environment. Run from build/start script.';

    private const JOB_NAME = 'test_year_bot_predictions';

    public function handle(): int
    {
        if (! Schema::hasTable('one_time_jobs')) {
            $this->warn('one_time_jobs table missing. Run migrations first.');

            return Command::SUCCESS;
        }

        $ran = DB::table('one_time_jobs')->where('name', self::JOB_NAME)->exists();
        if ($ran) {
            $this->info('Test-year bot predictions already seeded. Skipping.');

            return Command::SUCCESS;
        }

        $this->info('Seeding Last Year Order Bot predictions for 2025 and 2026...');
        config(['f1.bot_seed_seasons' => [2025, 2026]]);
        try {
            $seeder = new LastYearChampionshipOrderBotSeeder;
            $seeder->setCommand($this);
            $seeder->run();
        } finally {
            config(['f1.bot_seed_seasons' => null]);
        }

        DB::table('one_time_jobs')->insert([
            'name' => self::JOB_NAME,
            'run_at' => now(),
        ]);

        $this->info('Test-year bot predictions seeded. Flag set so this will not run again.');

        return Command::SUCCESS;
    }
}
