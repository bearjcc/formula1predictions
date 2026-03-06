<?php

namespace App\Console\Commands;

use Database\Seeders\DriverLineup2026Seeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureDriverLineupOnce extends Command
{
    protected $signature = 'app:ensure-driver-lineup-once';

    protected $description = 'Assign 2026 drivers to constructors once per environment. Run from build/start script.';

    private const JOB_NAME = 'driver_lineup_2026';

    public function handle(): int
    {
        if (! Schema::hasTable('one_time_jobs')) {
            $this->warn('one_time_jobs table missing. Run migrations first.');

            return Command::SUCCESS;
        }

        if (DB::table('one_time_jobs')->where('name', self::JOB_NAME)->exists()) {
            $this->info('Driver lineup 2026 already applied. Skipping.');

            return Command::SUCCESS;
        }

        $this->info('Applying 2026 driver lineup (assigning drivers to constructors)...');
        $seeder = new DriverLineup2026Seeder;
        $seeder->setCommand($this);
        $seeder->run();

        DB::table('one_time_jobs')->insert([
            'name' => self::JOB_NAME,
            'run_at' => now(),
        ]);

        $this->info('Driver lineup 2026 applied. Flag set so this will not run again.');

        return Command::SUCCESS;
    }
}
