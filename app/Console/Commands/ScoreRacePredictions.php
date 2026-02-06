<?php

namespace App\Console\Commands;

use App\Models\Races;
use App\Services\F1ApiService;
use App\Services\ScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScoreRacePredictions extends Command
{
    protected $signature = 'predictions:score 
                            {--race= : Specific race ID to score}
                            {--season= : Season to score (default: current year)}
                            {--round= : Specific race round to score}
                            {--all : Attempt to score all completed races that are unscored}
                            {--dry-run : Show actions without saving}';

    protected $description = 'Fetch race results and score active predictions';

    public function __construct(
        private F1ApiService $f1ApiService,
        private ScoringService $scoringService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $raceId = $this->option('race');
        $season = (int)($this->option('season') ?? date('Y'));
        $round = $this->option('round');
        $dryRun = $this->option('dry-run');

        if ($this->option('all')) {
            return $this->scoreUnscoredRaces($season, $dryRun);
        }

        if ($raceId) {
            $race = Races::findOrFail($raceId);
            return $this->processRace($race, $dryRun);
        }

        if ($round) {
            $race = Races::where('season', $season)->where('round', $round)->first();
            if (!$race) {
                $this->error("Race not found for season $season round $round");
                return 1;
            }
            return $this->processRace($race, $dryRun);
        }

        $this->warn("Please specify --race, --round, or --all.");
        return 1;
    }

    private function processRace(Races $race, bool $dryRun): int
    {
        $this->info("Processing {$race->race_name} ($race->season Round $race->round)...");

        // Try to fetch newest results from API first
        try {
            $apiData = $this->f1ApiService->getRaceResults($race->season, $race->round);
            if (!empty($apiData['races']['results'] ?? [])) {
                $this->info("Updating race results from official API...");
                if (!$dryRun) {
                    $race->update([
                        'results' => $apiData['races']['results'],
                        'status' => 'completed'
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->warn("Could not sync latest result from API: " . $e->getMessage());
        }

        if (!$race->isCompleted()) {
            $this->error("Race {$race->id} marked as incomplete. Cannot score.");
            return 1;
        }

        if ($dryRun) {
            $count = $race->predictions()->whereIn('status', ['submitted', 'locked'])->count();
            $this->info("[DRY RUN] Would score $count predictions.");
            return 0;
        }

        $results = $this->scoringService->scoreRacePredictions($race);
        
        $this->info("Scoring complete:");
        $this->line(" - Total: {$results['total_predictions']}");
        $this->line(" - Success: {$results['scored_predictions']}");
        $this->line(" - Failed: {$results['failed_predictions']}");

        return 0;
    }

    private function scoreUnscoredRaces(int $season, bool $dryRun): int
    {
        $races = Races::where('season', $season)
            ->where('status', 'completed')
            ->get();

        foreach ($races as $race) {
            $unscoredCount = $race->predictions()
                ->whereIn('status', ['submitted', 'locked'])
                ->count();

            if ($unscoredCount > 0) {
                $this->processRace($race, $dryRun);
            }
        }

        return 0;
    }
}
