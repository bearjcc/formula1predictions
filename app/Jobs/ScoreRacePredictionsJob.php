<?php

namespace App\Jobs;

use App\Models\Races;
use App\Services\PreviousRaceResultBotService;
use App\Services\RaceResultSyncService;
use App\Services\ScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScoreRacePredictionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $raceId,
        public bool $forceUpdate = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RaceResultSyncService $resultSyncService, ScoringService $scoringService): void
    {
        $race = Races::find($this->raceId);

        if (! $race) {
            Log::error('Race not found for scoring job', ['race_id' => $this->raceId]);

            return;
        }

        Log::info('Starting background scoring for race', [
            'race_id' => $race->id,
            'race_name' => $race->race_name,
            'season' => $race->season,
            'round' => $race->round,
        ]);

        try {
            // Update race results from API if needed
            if ($this->forceUpdate || ! $race->isCompleted()) {
                $resultSyncService->sync($race);
            }

            // Score predictions
            if ($race->isCompleted() && $race->getResultsArray() !== []) {
                $results = $scoringService->scoreRaceWeekendPredictions($race);

                Log::info('Background scoring completed', [
                    'race_id' => $race->id,
                    'total_predictions' => $results['total_predictions'],
                    'scored_predictions' => $results['scored_predictions'],
                    'failed_predictions' => $results['failed_predictions'],
                    'total_score' => $results['total_score'],
                ]);

                if (! empty($results['errors'])) {
                    Log::warning('Scoring errors encountered', [
                        'race_id' => $race->id,
                        'errors' => $results['errors'],
                    ]);
                }

                try {
                    /** @var PreviousRaceResultBotService $botService */
                    $botService = app(PreviousRaceResultBotService::class);
                    $botService->createNextRacePrediction($race);
                } catch (\Throwable $e) {
                    Log::error('Failed to create previous race bot prediction', [
                        'race_id' => $race->id,
                        'error' => $e->getMessage(),
                        'exception' => $e,
                    ]);
                }
            } else {
                Log::warning('Race not completed, skipping scoring', [
                    'race_id' => $race->id,
                    'status' => $race->status,
                    'results_count' => count($race->getResultsArray()),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Background scoring failed', [
                'race_id' => $race->id,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Scoring job failed permanently', [
            'race_id' => $this->raceId,
            'error' => $exception->getMessage(),
            'exception' => $exception,
        ]);
    }
}
