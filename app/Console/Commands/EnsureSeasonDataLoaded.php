<?php

namespace App\Console\Commands;

use App\Models\Drivers;
use App\Models\Races;
use App\Models\Teams;
use Illuminate\Console\Command;

class EnsureSeasonDataLoaded extends Command
{
    protected $signature = 'f1:ensure-season-data
                            {year? : Season year (default: config f1.current_season)}
                            {--force : Always run sync even if data exists}';

    protected $description = 'Ensure current year F1 data is loaded. Checks DB first; syncs from API only when missing. Run on deploy to avoid waiting for user interaction.';

    public function handle(): int
    {
        $year = (int) ($this->argument('year') ?? config('f1.current_season', (int) date('Y')));

        if (! $this->option('force') && $this->seasonDataExists($year)) {
            $this->info("Season {$year} data already loaded. Skipping sync.");

            return Command::SUCCESS;
        }

        $this->info("Season {$year} data missing or --force. Syncing from F1 API...");

        return $this->call('f1:sync-season', ['year' => $year]);
    }

    /**
     * Check if we have the minimal required data for the season.
     * Races are the primary indicator; drivers and teams are needed for prediction forms.
     */
    private function seasonDataExists(int $year): bool
    {
        $hasRaces = Races::where('season', $year)->exists();
        if (! $hasRaces) {
            return false;
        }

        $hasDrivers = Drivers::where('is_active', true)->exists();
        $hasTeams = Teams::where('is_active', true)->exists();

        return $hasDrivers && $hasTeams;
    }
}
