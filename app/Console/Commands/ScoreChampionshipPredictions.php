<?php

namespace App\Console\Commands;

use App\Services\ScoringService;
use Illuminate\Console\Command;

class ScoreChampionshipPredictions extends Command
{
    protected $signature = 'predictions:score-championship
                            {season : Season year (e.g. 2024)}
                            {--type=preseason : preseason or midseason}';

    protected $description = 'Score all preseason or midseason predictions for a season against final standings.';

    public function handle(ScoringService $scoring): int
    {
        $season = (int) $this->argument('season');
        $type = $this->option('type');

        if (! in_array($type, ['preseason', 'midseason'], true)) {
            $this->error('Type must be preseason or midseason.');

            return Command::FAILURE;
        }

        $this->info("Scoring {$type} predictions for season {$season}...");

        try {
            $results = $scoring->scoreChampionshipPredictions($season, $type);

            $this->info("Scored: {$results['scored_predictions']} predictions.");
            $this->info("Total score: {$results['total_score']}.");

            if ($results['failed_predictions'] > 0) {
                $this->warn("Failed: {$results['failed_predictions']}");
                foreach ($results['errors'] as $err) {
                    $this->line("  - {$err}");
                }
            }
        } catch (\Throwable $e) {
            $this->error("Scoring failed: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $this->info('Done.');

        return Command::SUCCESS;
    }
}
