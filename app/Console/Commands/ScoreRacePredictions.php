<?php

namespace App\Console\Commands;

use App\Models\Races;
use App\Services\F1ApiService;
use App\Services\ScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScoreRacePredictions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'predictions:score 
                            {--race= : Specific race ID to score}
                            {--season= : Season to score (default: current year)}
                            {--round= : Specific race round to score}
                            {--force : Force scoring even if already scored}
                            {--dry-run : Show what would be scored without actually scoring}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically score race predictions using F1 API results';

    public function __construct(
        private F1ApiService $f1ApiService,
        private ScoringService $scoringService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $raceId = $this->option('race');
        $season = $this->option('season') ?? date('Y');
        $round = $this->option('round');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        try {
            if ($raceId) {
                // Score specific race
                $race = Races::findOrFail($raceId);
                return $this->scoreSpecificRace($race, $force, $dryRun);
            } elseif ($round) {
                // Score specific round
                $race = Races::where('season', $season)
                    ->where('round', $round)
                    ->first();
                
                if (!$race) {
                    $this->error("No race found for season {$season}, round {$round}");
                    return 1;
                }
                
                return $this->scoreSpecificRace($race, $force, $dryRun);
            } else {
                // Score all completed races for the season
                return $this->scoreSeasonRaces($season, $force, $dryRun);
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("Scoring command failed: " . $e->getMessage());
            return 1;
        }
    }

    private function scoreSpecificRace(Races $race, bool $force, bool $dryRun): int
    {
        if (!$race->isCompleted()) {
            $this->error("Race {$race->id} is not completed yet");
            return 1;
        }

        $predictions = $race->predictions()
            ->whereIn('status', ['submitted', 'locked'])
            ->get();

        if ($predictions->isEmpty()) {
            $this->info("No predictions to score for race {$race->id}");
            return 0;
        }

        if ($dryRun) {
            $this->info("DRY RUN: Would score {$predictions->count()} predictions for race {$race->id}");
            return 0;
        }

        $this->info("Scoring {$predictions->count()} predictions for race {$race->id}...");
        
        $results = $this->scoringService->scoreRacePredictions($race);
        
        $this->info("Scored {$results['scored_predictions']} predictions successfully");
        $this->info("Total score: {$results['total_score']}");
        
        if ($results['failed_predictions'] > 0) {
            $this->warn("Failed to score {$results['failed_predictions']} predictions");
            foreach ($results['errors'] as $error) {
                $this->error($error);
            }
        }

        return 0;
    }

    private function scoreSeasonRaces(int $season, bool $force, bool $dryRun): int
    {
        $races = Races::where('season', $season)
            ->whereNotNull('results')
            ->get();

        if ($races->isEmpty()) {
            $this->info("No completed races found for season {$season}");
            return 0;
        }

        $totalPredictions = 0;
        $scoredRaces = 0;

        foreach ($races as $race) {
            $predictions = $race->predictions()
                ->whereIn('status', ['submitted', 'locked'])
                ->get();

            if ($predictions->isNotEmpty()) {
                $totalPredictions += $predictions->count();
                $scoredRaces++;
            }
        }

        if ($totalPredictions === 0) {
            $this->info("No predictions to score for season {$season}");
            return 0;
        }

        if ($dryRun) {
            $this->info("DRY RUN: Would score {$totalPredictions} predictions across {$scoredRaces} races");
            return 0;
        }

        $this->info("Scoring {$totalPredictions} predictions across {$scoredRaces} races for season {$season}...");

        foreach ($races as $race) {
            $predictions = $race->predictions()
                ->whereIn('status', ['submitted', 'locked'])
                ->get();

            if ($predictions->isNotEmpty()) {
                $this->info("Scoring race {$race->id} ({$predictions->count()} predictions)...");
                $results = $this->scoringService->scoreRacePredictions($race);
                $this->info("  - Scored: {$results['scored_predictions']}, Failed: {$results['failed_predictions']}");
            }
        }

        $this->info("Season scoring completed!");
        return 0;
    }
}
