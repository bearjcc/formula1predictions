<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Races;
use App\Notifications\PredictionScored;
use App\Services\Scoring\ChampionshipScoringService;
use App\Services\Scoring\RaceScoringService;
use Illuminate\Support\Facades\Log;

class ScoringService
{
    public function __construct(
        private F1ApiService $f1ApiService,
        private RaceScoringService $raceScoring,
        private ChampionshipScoringService $championshipScoring
    ) {}

    /**
     * Automatically score all predictions for a completed race
     */
    public function scoreRacePredictions(Races $race): array
    {
        if (! $race->isCompleted()) {
            throw new \InvalidArgumentException("Race {$race->id} is not completed");
        }

        $predictions = $race->predictions()
            ->with('user')
            ->whereIn('status', ['submitted', 'locked'])
            ->get();

        $results = [
            'total_predictions' => $predictions->count(),
            'scored_predictions' => 0,
            'failed_predictions' => 0,
            'total_score' => 0,
            'errors' => [],
        ];

        foreach ($predictions as $prediction) {
            try {
                $score = $this->raceScoring->calculateRaceScore($prediction, $race);
                $this->savePredictionScore($prediction, $score);

                $results['scored_predictions']++;
                $results['total_score'] += $score;

                try {
                    $prediction->user->notify(new PredictionScored($prediction, $score, $prediction->accuracy));
                } catch (\Exception $e) {
                    Log::warning("Could not notify user {$prediction->user_id} for prediction {$prediction->id}");
                }
            } catch (\Exception $e) {
                $results['failed_predictions']++;
                $results['errors'][] = "Prediction {$prediction->id}: ".$e->getMessage();
                Log::error("Failed to score prediction {$prediction->id}: ".$e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Score all race-weekend predictions for a race, including sprint predictions when applicable.
     *
     * @return array{total_predictions: int, scored_predictions: int, failed_predictions: int, total_score: int, errors: list<string>}
     */
    public function scoreRaceWeekendPredictions(Races $race): array
    {
        if (! $race->isCompleted()) {
            throw new \InvalidArgumentException("Race {$race->id} is not completed");
        }

        if ($race->getResultsArray() === []) {
            throw new \InvalidArgumentException("Race {$race->id} has no results to score against");
        }

        $raceResults = $this->scoreRacePredictions($race);
        $sprintResults = $race->hasSprint()
            ? $this->scoreSprintPredictions($race)
            : [
                'total_predictions' => 0,
                'scored_predictions' => 0,
                'failed_predictions' => 0,
                'total_score' => 0,
                'errors' => [],
            ];

        return [
            'total_predictions' => $raceResults['total_predictions'] + $sprintResults['total_predictions'],
            'scored_predictions' => $raceResults['scored_predictions'] + $sprintResults['scored_predictions'],
            'failed_predictions' => $raceResults['failed_predictions'] + $sprintResults['failed_predictions'],
            'total_score' => $raceResults['total_score'] + $sprintResults['total_score'],
            'errors' => array_values(array_merge($raceResults['errors'], $sprintResults['errors'])),
        ];
    }

    /**
     * Automatically score all sprint predictions for a completed sprint race.
     */
    public function scoreSprintPredictions(Races $race): array
    {
        if (! $race->isCompleted() || ! $race->hasSprint()) {
            throw new \InvalidArgumentException("Race {$race->id} is not a completed sprint race");
        }

        $predictions = $race->sprintPredictions()
            ->with('user')
            ->whereIn('status', ['submitted', 'locked'])
            ->get();

        $results = [
            'total_predictions' => $predictions->count(),
            'scored_predictions' => 0,
            'failed_predictions' => 0,
            'total_score' => 0,
            'errors' => [],
        ];

        foreach ($predictions as $prediction) {
            try {
                $score = $this->raceScoring->calculateSprintScore($prediction, $race);
                $this->savePredictionScore($prediction, $score);

                $results['scored_predictions']++;
                $results['total_score'] += $score;

                try {
                    $prediction->user->notify(new PredictionScored($prediction, $score, $prediction->accuracy));
                } catch (\Exception $e) {
                    Log::warning("Could not notify user {$prediction->user_id} for sprint prediction {$prediction->id}");
                }
            } catch (\Exception $e) {
                $results['failed_predictions']++;
                $results['errors'][] = "Sprint prediction {$prediction->id}: ".$e->getMessage();
                Log::error("Failed to score sprint prediction {$prediction->id}: ".$e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Calculate score for a single prediction with full edge case handling
     */
    public function calculatePredictionScore(Prediction $prediction, Races $race): int
    {
        return $this->raceScoring->calculateRaceScore($prediction, $race);
    }

    /**
     * Calculate score for a sprint prediction.
     */
    public function calculateSprintPredictionScore(Prediction $prediction, Races $race): int
    {
        return $this->raceScoring->calculateSprintScore($prediction, $race);
    }

    /**
     * Calculate score for a preseason or midseason championship prediction.
     * Preseason: constructor order (preseason points table), teammate battles (+5 each), red flags/safety cars (if actuals set).
     * Midseason: team + driver order (race position table), +50 perfect bonus.
     */
    public function calculateChampionshipPredictionScore(Prediction $prediction, int $season): int
    {
        return $this->championshipScoring->calculateChampionshipScore($prediction, $season);
    }

    /**
     * Score all preseason or midseason predictions for a season.
     *
     * @return array{total_predictions: int, scored_predictions: int, failed_predictions: int, total_score: int, errors: list<string>}
     */
    public function scoreChampionshipPredictions(int $season, string $type = 'preseason'): array
    {
        if (! in_array($type, ['preseason', 'midseason'], true)) {
            throw new \InvalidArgumentException("Type must be preseason or midseason, got: {$type}");
        }

        $predictions = Prediction::where('type', $type)
            ->where('season', $season)
            ->whereIn('status', ['submitted', 'locked'])
            ->get();

        $results = [
            'total_predictions' => $predictions->count(),
            'scored_predictions' => 0,
            'failed_predictions' => 0,
            'total_score' => 0,
            'errors' => [],
        ];

        foreach ($predictions as $prediction) {
            try {
                $score = $this->championshipScoring->calculateChampionshipScore($prediction, $season);
                $this->savePredictionScore($prediction, $score);

                $results['scored_predictions']++;
                $results['total_score'] += $score;

                try {
                    $prediction->user->notify(new PredictionScored($prediction, $score, $prediction->accuracy));
                } catch (\Exception $e) {
                    Log::warning("Could not notify user {$prediction->user_id} for prediction {$prediction->id}");
                }
            } catch (\Exception $e) {
                $results['failed_predictions']++;
                $results['errors'][] = "Prediction {$prediction->id}: ".$e->getMessage();
                Log::error("Failed to score championship prediction {$prediction->id}: ".$e->getMessage());
            }
        }

        return $results;
    }

    public function savePredictionScore(Prediction $prediction, int $score): void
    {
        $prediction->forceFill([
            'score' => $score,
            'accuracy' => $this->calculateAccuracyValue($prediction),
            'status' => 'scored',
            'scored_at' => now(),
        ])->save();
    }

    public function calculateAccuracyValue(Prediction $prediction): float
    {
        return $this->raceScoring->calculateAccuracy($prediction);
    }

    /**
     * Get per-driver and fastest-lap breakdown for a scored race/sprint prediction (for display).
     *
     * @return array{total: int, half_points: bool, fastest_lap_row: array{predicted_driver_id: string|null, actual_driver_id: string|null, points: int}, driver_rows: list<array{position: int, predicted_driver_id: string, actual_display: string, diff: int|null, points: int}>, dnf_wager_points: int, perfect_bonus: int}
     */
    public function getPredictionBreakdown(Prediction $prediction, Races $race): array
    {
        return $this->raceScoring->buildBreakdown($prediction, $race);
    }

    /**
     * Admin override: manually set prediction score
     */
    public function overridePredictionScore(Prediction $prediction, int $score, ?string $reason = null): void
    {
        $prediction->forceFill([
            'score' => $score,
            'accuracy' => $this->calculateAccuracyValue($prediction),
            'status' => 'scored',
            'scored_at' => now(),
            'notes' => $reason ? "Admin override: {$reason}" : 'Admin override',
        ])->save();

        // Send notification
        $prediction->user->notify(new PredictionScored($prediction, $score, $prediction->accuracy));
    }

    /**
     * Handle driver substitutions for a race
     */
    public function handleDriverSubstitutions(Races $race, array $substitutions): void
    {
        // Apply substitutions across the full race weekend so sprint and race
        // predictions stay in sync when reserve drivers step in.
        $predictions = $race->predictions()
            ->whereIn('status', ['submitted', 'locked'])
            ->get()
            ->concat(
                $race->sprintPredictions()
                    ->whereIn('status', ['submitted', 'locked'])
                    ->get()
            );

        foreach ($predictions as $prediction) {
            $predictionData = $prediction->prediction_data;
            $driverOrder = $predictionData['driver_order'] ?? [];
            $updated = false;

            // Check for substitutions
            foreach ($substitutions as $oldDriverId => $newDriverId) {
                $key = array_search($oldDriverId, $driverOrder);
                if ($key !== false) {
                    $driverOrder[$key] = $newDriverId;
                    $updated = true;
                }
            }

            if ($updated) {
                $predictionData['driver_order'] = $driverOrder;
                $prediction->update([
                    'prediction_data' => $predictionData,
                    'notes' => 'Driver substitution applied',
                ]);
            }
        }
    }

    /**
     * Handle race cancellation
     */
    public function handleRaceCancellation(Races $race, ?string $reason = null): void
    {
        $predictions = $race->predictions()
            ->whereIn('status', ['submitted', 'locked'])
            ->get()
            ->concat(
                $race->sprintPredictions()
                    ->whereIn('status', ['submitted', 'locked'])
                    ->get()
            );

        foreach ($predictions as $prediction) {
            $prediction->forceFill([
                'status' => 'cancelled',
                'score' => 0,
                'accuracy' => 0,
                'notes' => $reason ? "Race cancelled: {$reason}" : 'Race cancelled',
            ])->save();
        }

        $race->forceFill([
            'status' => 'cancelled',
            'results' => [],
        ])->save();
    }

    /**
     * Get scoring statistics for a race
     */
    public function getRaceScoringStats(Races $race): array
    {
        $predictions = $race->predictions()->where('status', 'scored')->get();

        if ($predictions->isEmpty()) {
            return [
                'total_predictions' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'perfect_predictions' => 0,
            ];
        }

        $scores = $predictions->pluck('score')->toArray();
        $maxScore = max($scores);
        $perfectPredictions = $predictions->where('score', $maxScore)->count();

        return [
            'total_predictions' => $predictions->count(),
            'average_score' => round(array_sum($scores) / count($scores), 2),
            'highest_score' => $maxScore,
            'lowest_score' => min($scores),
            'perfect_predictions' => $perfectPredictions,
        ];
    }
}
