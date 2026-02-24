<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckReady extends Command
{
    protected $signature = 'app:check-ready';

    protected $description = 'Verify critical config and app state (for CI and pre-deploy).';

    public function handle(): int
    {
        $failed = false;

        if (empty(config('app.key')) || config('app.key') === 'base64:') {
            $this->error('APP_KEY is not set. Run php artisan key:generate.');
            $failed = true;
        }

        $season = config('f1.current_season');
        $year = (int) date('Y');
        if (! is_int($season) || $season < 2020 || $season > $year + 1) {
            $this->error("f1.current_season must be an integer between 2020 and {$year}+1, got: ".var_export($season, true));
            $failed = true;
        }

        if ($failed) {
            return Command::FAILURE;
        }

        $this->info('Readiness check passed (APP_KEY, f1.current_season).');

        return Command::SUCCESS;
    }
}
