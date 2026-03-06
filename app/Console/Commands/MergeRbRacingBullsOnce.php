<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MergeRbRacingBullsOnce extends Command
{
    protected $signature = 'app:merge-rb-racing-bulls-once';

    protected $description = 'Run RB/Racing Bulls merge migration once per environment. Run from build/start script.';

    private const JOB_NAME = 'merge_rb_racing_bulls_duplicate_teams';

    private const MIGRATION_PATH = 'database/migrations/2026_03_06_000000_merge_rb_racing_bulls_duplicate_teams.php';

    public function handle(): int
    {
        if (! Schema::hasTable('one_time_jobs')) {
            $this->warn('one_time_jobs table missing. Run migrations first.');

            return Command::SUCCESS;
        }

        if (DB::table('one_time_jobs')->where('name', self::JOB_NAME)->exists()) {
            $this->info('RB/Racing Bulls merge already run. Skipping.');

            return Command::SUCCESS;
        }

        $this->info('Running RB/Racing Bulls merge migration once...');
        Artisan::call('migrate', [
            '--path' => self::MIGRATION_PATH,
            '--force' => true,
        ]);

        DB::table('one_time_jobs')->insert([
            'name' => self::JOB_NAME,
            'run_at' => now(),
        ]);

        $this->info('RB/Racing Bulls merge completed. Flag set so this will not run again.');

        return Command::SUCCESS;
    }
}
