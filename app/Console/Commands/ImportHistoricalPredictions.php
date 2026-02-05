<?php

namespace App\Console\Commands;

use Database\Seeders\HistoricalPredictionsSeeder;
use Illuminate\Console\Command;

class ImportHistoricalPredictions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:import-historical-predictions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import historical predictions from markdown fixtures using the legacy Phase 1 pipeline';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Running historical predictions import...');

        try {
            /** @var \Database\Seeders\HistoricalPredictionsSeeder $seeder */
            $seeder = app(HistoricalPredictionsSeeder::class);
            $seeder->run();

            $this->info('Historical predictions import completed successfully.');

            return 0;
        } catch (\Throwable $exception) {
            $this->error('Historical predictions import failed: '.$exception->getMessage());

            return 1;
        }
    }
}
