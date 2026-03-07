<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off audit: list all race 1 (round 1) predictions and verify prediction_data
 * has driver_order, fastest_lap, and dnf_predictions stored correctly.
 *
 * Usage:
 *   railway run php artisan predictions:audit-race1   (audit live DB via Railway env)
 *   PRODUCTION_DATABASE_URL="mysql://..." php artisan predictions:audit-race1 --connection=production
 */
class AuditRace1Predictions extends Command
{
    protected $signature = 'predictions:audit-race1
                            {--connection= : DB connection (default = default connection, e.g. production when run via Railway)}
                            {--season= : Season year (default: all race-1 predictions)}';

    protected $description = 'Audit race 1 predictions for order, DNF, fastest_lap';

    public function handle(): int
    {
        $connectionName = $this->option('connection') ?? config('database.default');
        $conn = DB::connection($connectionName);

        try {
            $conn->getPdo();
        } catch (\Throwable $e) {
            $this->error('Cannot connect to database ('.$connectionName.'): '.$e->getMessage());

            return self::FAILURE;
        }

        $season = $this->option('season') ? (int) $this->option('season') : null;

        $query = $conn->table('predictions')
            ->where('type', 'race')
            ->where('race_round', 1)
            ->orderBy('season')
            ->orderBy('user_id');

        if ($season !== null) {
            $query->where('season', $season);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->warn('No race 1 predictions found.');

            return self::SUCCESS;
        }

        $this->info('Race 1 predictions: '.$rows->count());
        $this->newLine();

        $userIds = $rows->pluck('user_id')->unique()->values()->all();
        $users = $conn->table('users')->whereIn('id', $userIds)->get()->keyBy('id');

        $issues = [];
        $table = [];
        foreach ($rows as $row) {
            $data = is_string($row->prediction_data)
                ? json_decode($row->prediction_data, true)
                : (array) $row->prediction_data;
            $data = $data ?? [];

            $order = $data['driver_order'] ?? [];
            $orderCount = is_array($order) ? count($order) : 0;
            $fastestLap = $data['fastest_lap'] ?? null;
            $dnf = $data['dnf_predictions'] ?? [];
            $dnfCount = is_array($dnf) ? count($dnf) : 0;

            $user = $users->get($row->user_id);
            $name = $user ? ($user->name ?? $user->email ?? 'user#'.$row->user_id) : 'user#'.$row->user_id;

            $orderOk = $orderCount >= 1;
            $fastestOk = $fastestLap !== null && $fastestLap !== '';
            $dnfOk = true; // dnf is optional (can be empty)

            if (! $orderOk) {
                $issues[] = "id={$row->id} ({$name}): missing or empty driver_order";
            }
            // fastest_lap is optional; list for visibility but not as a hard issue
            if (! $fastestOk) {
                $issues[] = "id={$row->id} ({$name}): no fastest_lap (optional)";
            }

            $table[] = [
                $row->id,
                $row->season,
                $name,
                $row->status,
                $orderCount,
                $fastestOk ? 'yes' : 'no',
                $dnfCount,
            ];
        }

        $this->table(
            ['id', 'season', 'user', 'status', 'driver_order_count', 'fastest_lap_set', 'dnf_count'],
            $table
        );

        if (! empty($issues)) {
            $this->newLine();
            $this->warn('Notes / issues:');
            foreach ($issues as $issue) {
                $this->line('  - '.$issue);
            }
            $this->line('  (driver_order is required; fastest_lap and dnf_predictions are optional.)');
        }

        return self::SUCCESS;
    }
}
