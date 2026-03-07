<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copy reference data (countries, circuits, teams, drivers, races) from production MySQL
 * into the local database so the preseason form and other pages match production data.
 *
 * Set PRODUCTION_DATABASE_URL in .env (e.g. mysql://user:pass@host:port/database).
 * Local DB is the default connection (e.g. SQLite). Users and predictions are not touched.
 */
class SyncFromProduction extends Command
{
    protected $signature = 'db:sync-from-production
                            {--dry-run : Show what would be copied without writing}';

    protected $description = 'Copy countries, circuits, teams, drivers, and races from production to local DB';

    /** @var array<int, string> Tables in dependency order (no FKs first, then dependents). */
    private const TABLES_COPY_ORDER = ['countries', 'circuits', 'teams', 'drivers', 'races'];

    /** @var array<int, string> Reverse order for truncate (dependents first). */
    private const TABLES_DELETE_ORDER = ['races', 'drivers', 'teams', 'circuits', 'countries'];

    public function handle(): int
    {
        if (! config('database.connections.production.url') && ! config('database.connections.production.host')) {
            $this->error('Production DB not configured. Set PRODUCTION_DATABASE_URL (or DB_PRODUCTION_*) in .env');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $local = DB::connection();
        $driver = $local->getDriverName();

        if ($dryRun) {
            $this->warn('Dry run: no changes will be written.');
        }

        try {
            $prod = DB::connection('production');
            $prod->getPdo();
        } catch (\Throwable $e) {
            $this->error('Cannot connect to production: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($driver === 'sqlite' && ! $dryRun) {
            $local->statement('PRAGMA foreign_keys = OFF');
        }

        try {
            if (! $dryRun) {
                foreach (self::TABLES_DELETE_ORDER as $table) {
                    if (! Schema::connection(null)->hasTable($table)) {
                        continue;
                    }
                    $local->table($table)->delete();
                    $this->line("Cleared local <info>{$table}</info>.");
                }
            }

            foreach (self::TABLES_COPY_ORDER as $table) {
                if (! $prod->getSchemaBuilder()->hasTable($table)) {
                    $this->warn("Production has no table [{$table}], skipping.");

                    continue;
                }

                $rows = $prod->table($table)->orderBy('id')->get();
                $count = $rows->count();
                if ($count === 0) {
                    $this->line("Production <info>{$table}</info>: 0 rows.");
                    continue;
                }

                $array = $rows->map(fn ($r) => (array) $r)->all();

                if (! $dryRun) {
                    $chunkSize = 100;
                    foreach (array_chunk($array, $chunkSize) as $chunk) {
                        $local->table($table)->insert($chunk);
                    }
                    $this->updateSqliteSequence($local, $table, $driver);
                }

                $this->line("Production <info>{$table}</info>: {$count} row(s)".($dryRun ? ' (not written)' : ' copied.'));
            }

            if ($driver === 'sqlite' && ! $dryRun) {
                $local->statement('PRAGMA foreign_keys = ON');
            }

            $this->info('Sync complete. Local reference data now matches production.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            if ($driver === 'sqlite' && ! $dryRun) {
                $local->statement('PRAGMA foreign_keys = ON');
            }
            $this->error('Sync failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function updateSqliteSequence($connection, string $table, string $driver): void
    {
        if ($driver !== 'sqlite') {
            return;
        }
        $max = $connection->table($table)->max('id');
        if ($max !== null) {
            $connection->statement("DELETE FROM sqlite_sequence WHERE name = ?", [$table]);
            $connection->statement('INSERT INTO sqlite_sequence (name, seq) VALUES (?, ?)', [$table, (int) $max]);
        }
    }
}
