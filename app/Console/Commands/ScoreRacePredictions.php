<?php

namespace App\Console\Commands;

use App\Models\Races;
use App\Services\RaceResultSyncService;
use App\Services\ScoringService;
use Illuminate\Console\Command;

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
        private RaceResultSyncService $resultSyncService,
        private ScoringService $scoringService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $raceId = $this->option('race');
        $season = (int) ($this->option('season') ?? date('Y'));
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
            if (! $race) {
                $this->error("Race not found for season $season round $round");

                return 1;
            }

            return $this->processRace($race, $dryRun);
        }

        $this->warn('Please specify --race, --round, or --all.');

        return 1;
    }

    private function processRace(Races $race, bool $dryRun): int
    {
        $this->info("Processing {$race->display_name} ($race->season Round $race->round)...");

        // Try to fetch newest results from API first
        try {
            if ($dryRun) {
                $this->info('[DRY RUN] Would sync latest race results from official API.');
            } else {
                $synced = $this->resultSyncService->sync($race);
                $this->info("Synced {$synced['result_count']} result rows from official API.");
            }
        } catch (\Exception $e) {
            $this->warn('Could not sync latest result from API: '.$e->getMessage());
        }

        if (! $race->isCompleted()) {
            $this->error("Race {$race->id} marked as incomplete. Cannot score.");

            return 1;
        }

        if ($race->getResultsArray() === []) {
            $this->error("Race {$race->id} has no stored results. Cannot score.");

            return 1;
        }

        if ($dryRun) {
            $count = $race->predictions()->whereIn('status', ['submitted', 'locked'])->count()
                + $race->sprintPredictions()->whereIn('status', ['submitted', 'locked'])->count();
            $this->info("[DRY RUN] Would score $count predictions.");

            return 0;
        }

        $results = $this->scoringService->scoreRaceWeekendPredictions($race);

        $this->info('Scoring complete:');
        $this->line(" - Total: {$results['total_predictions']}");
        $this->line(" - Success: {$results['scored_predictions']}");
        $this->line(" - Failed: {$results['failed_predictions']}");

        return 0;
    }

    private function scoreUnscoredRaces(int $season, bool $dryRun): int
    {
        // Races already marked complete with unscored predictions
        $completedRaces = Races::where('season', $season)
            ->where('status', 'completed')
            ->get()
            ->filter(fn (Races $r) => $r->predictions()
                ->whereIn('status', ['submitted', 'locked'])
                ->exists()
                || $r->sprintPredictions()
                    ->whereIn('status', ['submitted', 'locked'])
                    ->exists()
            );

        // Races not yet marked complete but started 6+ hours ago — attempt to fetch results
        $potentiallyDone = Races::where('season', $season)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q): void {
                $q->where('time', '<=', now()->subHours(6))
                    ->orWhere(function ($q2): void {
                        // Fallback: no time stored, use race date (treat end-of-day as done)
                        $q2->whereNull('time')
                            ->where('date', '<', now()->subHours(6)->toDateString());
                    });
            })
            ->get();

        $races = $completedRaces->merge($potentiallyDone)->unique('id');

        if ($races->isEmpty()) {
            $this->info('No races to score.');

            return 0;
        }

        foreach ($races as $race) {
            $this->processRace($race, $dryRun);
        }

        return 0;
    }
}
