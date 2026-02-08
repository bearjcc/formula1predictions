<?php

namespace App\Jobs;

use App\Models\Races;
use App\Services\F1ApiService;
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
    public function handle(F1ApiService $f1ApiService, ScoringService $scoringService): void
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
                $this->updateRaceResults($race, $f1ApiService);
            }

            // Score predictions
            if ($race->isCompleted()) {
                $results = $scoringService->scoreRacePredictions($race);

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
            } else {
                Log::warning('Race not completed, skipping scoring', [
                    'race_id' => $race->id,
                    'status' => $race->status,
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
     * Update race results from F1 API
     */
    private function updateRaceResults(Races $race, F1ApiService $f1ApiService): void
    {
        try {
            $apiResults = $f1ApiService->getRaceResults($race->season, $race->round);

            if (isset($apiResults['races']['results']) && ! empty($apiResults['races']['results'])) {
                $race->update([
                    'results' => $apiResults['races']['results'],
                    'status' => 'completed',
                ]);

                Log::info('Race results updated from API', [
                    'race_id' => $race->id,
                    'results_count' => count($apiResults['races']['results']),
                ]);
            } else {
                Log::warning('No results found in API response', [
                    'race_id' => $race->id,
                    'api_response' => $apiResults,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update race results from API', [
                'race_id' => $race->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
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
