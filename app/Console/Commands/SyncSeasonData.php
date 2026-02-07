<?php

namespace App\Console\Commands;

use App\Services\F1ApiService;
use Illuminate\Console\Command;

class SyncSeasonData extends Command
{
    protected $signature = 'f1:sync-season
                            {year? : Season year (default: config f1.current_season)}
                            {--races-only : Only sync races from schedule}
                            {--drivers-only : Only sync drivers from championship}
                            {--teams-only : Only sync teams from championship}';

    protected $description = 'Sync 2026 (or given year) season data: races from schedule, drivers and teams from championship standings.';

    public function handle(F1ApiService $f1): int
    {
        $year = (int) ($this->argument('year') ?? config('f1.current_season', 2025));
        $racesOnly = $this->option('races-only');
        $driversOnly = $this->option('drivers-only');
        $teamsOnly = $this->option('teams-only');
        $all = ! $racesOnly && ! $driversOnly && ! $teamsOnly;

        $this->info("Syncing season data for {$year}...");

        try {
            if ($all || $racesOnly) {
                $result = $f1->syncSeasonRacesFromSchedule($year);
                $this->info("Races: {$result['created']} created, {$result['updated']} updated.");
            }

            if ($all || $teamsOnly) {
                $teams = $f1->syncTeamsForSeason($year);
                $this->info("Teams: {$teams} synced.");
            }

            if ($all || $driversOnly) {
                $drivers = $f1->syncDriversForSeason($year);
                $this->info("Drivers: {$drivers} synced.");
            }
        } catch (\Throwable $e) {
            $this->error("Sync failed: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $this->info('Done.');

        return Command::SUCCESS;
    }
}
