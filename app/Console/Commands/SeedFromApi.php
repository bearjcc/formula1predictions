<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Drivers;
use App\Models\Teams;
use App\Services\F1ApiService;
use Illuminate\Console\Command;

/**
 * Seed local DB from the same F1 API used in production (https://f1api.dev).
 * Runs the same steps as start-container.sh: sync season data then apply driver lineup.
 */
class SeedFromApi extends Command
{
    protected $signature = 'db:seed-from-api
                            {year? : Season year (default: config f1.current_season)}
                            {--force : Run sync even if season data already exists}
                            {--replace : After sync, deactivate drivers and teams not in the API response for this season}';

    protected $description = 'Seed teams, drivers, and races from F1 API (same source as production)';

    public function handle(F1ApiService $f1): int
    {
        $year = (int) ($this->argument('year') ?? config('f1.current_season'));
        $force = $this->option('force');
        $replace = $this->option('replace');

        $this->info("Seeding from F1 API (https://f1api.dev) for season {$year}...");

        $exit = $this->call('f1:ensure-season-data', [
            'year' => $year,
            '--force' => $force,
        ]);
        if ($exit !== self::SUCCESS) {
            return $exit;
        }

        // Only apply the 2026 driver-to-constructor lineup when seeding 2026.
        // Running it after seeding 2025 would overwrite 2025 team assignments with 2026.
        if ($year === 2026) {
            $exit = $this->call('app:ensure-driver-lineup-once');
            if ($exit !== self::SUCCESS) {
                return $exit;
            }
        }

        if ($replace) {
            $this->deactivateExtras($f1, $year);
        }

        $this->info('Done. Local reference data is now seeded from the F1 API (same as production).');

        return self::SUCCESS;
    }

    /**
     * Deactivate teams and drivers that are not in the API response for this season.
     * Leaves rows in place (predictions may reference them); only is_active is set to false.
     */
    private function deactivateExtras(F1ApiService $f1, int $year): void
    {
        $teamIds = $f1->getTeamIdsInSeason($year);
        $driverIds = $f1->getDriverIdsInSeason($year);

        if ($teamIds === [] && $driverIds === []) {
            $this->warn("Could not get team/driver IDs for {$year} from API. Skipping --replace.");

            return;
        }

        if ($teamIds !== []) {
            $deactivatedTeams = Teams::whereNotIn('team_id', $teamIds)->update(['is_active' => false]);
            Teams::whereIn('team_id', $teamIds)->update(['is_active' => true]);
            $this->line("Teams: ".count($teamIds)." active for {$year}, {$deactivatedTeams} deactivated.");
        }

        if ($driverIds !== []) {
            $deactivatedDrivers = Drivers::whereNotIn('driver_id', $driverIds)->update(['is_active' => false]);
            Drivers::whereIn('driver_id', $driverIds)->update(['is_active' => true]);
            $this->line("Drivers: ".count($driverIds)." active for {$year}, {$deactivatedDrivers} deactivated.");
        }
    }
}
