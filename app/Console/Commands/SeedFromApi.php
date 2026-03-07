<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Seed local DB from the same F1 API used in production (https://f1api.dev).
 * Runs the same steps as start-container.sh: sync season data then apply driver lineup.
 */
class SeedFromApi extends Command
{
    protected $signature = 'db:seed-from-api
                            {year? : Season year (default: config f1.current_season)}
                            {--force : Run sync even if season data already exists}';

    protected $description = 'Seed teams, drivers, and races from F1 API (same source as production)';

    public function handle(): int
    {
        $year = (int) ($this->argument('year') ?? config('f1.current_season'));
        $force = $this->option('force');

        $this->info("Seeding from F1 API (https://f1api.dev) for season {$year}...");

        $exit = $this->call('f1:ensure-season-data', [
            'year' => $year,
            '--force' => $force,
        ]);
        if ($exit !== self::SUCCESS) {
            return $exit;
        }

        $exit = $this->call('app:ensure-driver-lineup-once');
        if ($exit !== self::SUCCESS) {
            return $exit;
        }

        $this->info('Done. Local reference data is now seeded from the F1 API (same as production).');

        return self::SUCCESS;
    }
}
