<?php

namespace App\Console\Commands;

use App\Services\F1ApiService;
use Illuminate\Console\Command;

class SyncRaceSchedule extends Command
{
    protected $signature = 'f1:sync-schedule {year? : Season year (default: config f1.current_season)}';

    protected $description = 'Sync qualifying and sprint qualifying times from F1 API to races table.';

    public function handle(F1ApiService $f1): int
    {
        $year = (int) ($this->argument('year') ?? config('f1.current_season', 2025));

        $this->info("Syncing race schedule for {$year}...");

        try {
            $updated = $f1->syncScheduleToRaces($year);
            $this->info("Updated {$updated} race(s) with qualifying/sprint times.");
        } catch (\Throwable $e) {
            $this->error("Sync failed: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
