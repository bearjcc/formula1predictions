<?php

namespace App\Console\Commands;

use App\Models\Prediction;
use App\Models\Races;
use App\Services\ScoringService;
use Illuminate\Console\Command;

class ScoreHistoricalPredictions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:score-historical-predictions
                            {--season= : Specific season to score}
                            {--dry-run : Show what would be scored without actually scoring}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Score historical predictions for completed races';

    public function __construct(
        private ScoringService $scoringService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $season = $this->option('season');
        $dryRun = $this->option('dry-run');

        $this->info('Scoring historical predictions...');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        // Get completed races that haven't been scored yet
        $query = Races::where('status', 'completed')
            ->whereNotNull('results')
            ->where('results', '!=', '[]');

        if ($season) {
            $query->where('season', $season);
        }

        $races = $query->orderBy('season')->orderBy('round')->get();

        if ($races->isEmpty()) {
            $this->warn('No completed races found to score.');
            return 0;
        }

        $this->info("Found {$races->count()} races to process");

        $totalProcessed = 0;
        $totalScored = 0;

        foreach ($races as $race) {
            $this->processRace($race, $dryRun, $totalProcessed, $totalScored);
        }

        $this->info("Completed! Processed {$totalProcessed} predictions, scored {$totalScored} predictions");

        return 0;
    }

    private function processRace(Races $race, bool $dryRun, int &$totalProcessed, int &$totalScored): void
    {
        $this->info("Processing race: {$race->season} Round {$race->round} - {$race->race_name}");

        // Get predictions that need scoring for this race
        $predictions = $race->predictions()
            ->whereIn('status', ['submitted', 'locked'])
            ->whereNull('scored_at')
            ->get();

        if ($predictions->isEmpty()) {
            $this->line("  No predictions to score for this race");
            return;
        }

        $this->line("  Found {$predictions->count()} predictions to score");

        foreach ($predictions as $prediction) {
            $totalProcessed++;

            if ($dryRun) {
                $score = $this->scoringService->calculatePredictionScore($prediction, $race);
                $this->line("    {$prediction->user->name}: {$score} points (not saved)");
            } else {
                try {
                    $score = $this->scoringService->calculatePredictionScore($prediction, $race);
                    $accuracy = $prediction->calculateAccuracy();
                    $prediction->update([
                        'score' => $score,
                        'accuracy' => $accuracy,
                        'status' => 'scored',
                        'scored_at' => now(),
                    ]);
                    $totalScored++;
                    $this->line("    {$prediction->user->name}: {$score} points ✓");
                } catch (\Exception $e) {
                    $this->error("    {$prediction->user->name}: Error - {$e->getMessage()}");
                }
            }
        }
    }
}
