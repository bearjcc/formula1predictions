<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Races;
use App\Notifications\PredictionScored;
use Illuminate\Support\Facades\Log;

class ScoringService
{
    public function __construct(
        private F1ApiService $f1ApiService
    ) {}

    /**
     * Automatically score all predictions for a completed race
     */
    public function scoreRacePredictions(Races $race): array
    {
        if (!$race->isCompleted()) {
            throw new \InvalidArgumentException("Race {$race->id} is not completed");
        }

        $predictions = $race->predictions()
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
                $score = $this->calculatePredictionScore($prediction, $race);
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
                $results['errors'][] = "Prediction {$prediction->id}: " . $e->getMessage();
                Log::error("Failed to score prediction {$prediction->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Calculate score for a single prediction with full edge case handling
     */
    public function calculatePredictionScore(Prediction $prediction, Races $race): int
    {
        if ($prediction->type !== 'race') {
            return 0;
        }

        $predictedOrder = $prediction->getPredictedDriverOrder();
        $actualResults = $this->processRaceResults($race->getResultsArray());
        
        if (empty($actualResults)) {
            return 0;
        }

        $score = 0;
        $totalDrivers = count($predictedOrder);
        $correctCount = 0;

        foreach ($predictedOrder as $position => $driverId) {
            $actualData = $this->findDriverInProcessedResults($driverId, $actualResults);
            
            if ($actualData !== null) {
                $actualPosition = $actualData['position'];
                $positionDiff = abs($position - $actualPosition);
                $positionScore = $this->getPositionScore($positionDiff, $race->season);
                $score += $positionScore;
                
                if ($positionDiff === 0) {
                    $correctCount++;
                }

                // DNF penalty if predicted high but DNF'd
                if ($actualData['status'] === 'DNF' && $position < 10) {
                    $score -= 5;
                }
            } else {
                // Driver not in results (DNS, DSQ, etc.)
                $score += $this->getMissingDriverScore($driverId, $actualResults, $race->season);
            }
        }

        // Fastest lap bonus
        $fastestLapScore = $this->calculateFastestLapScore($prediction, $actualResults);
        $score += $fastestLapScore;

        // Perfect prediction bonus (Top 10)
        if ($correctCount >= 10) {
            $score += 50;
        }

        return $score;
    }

    /**
     * Process race results to handle edge cases like DNF and filter DNS
     */
    private function processRaceResults(array $results): array
    {
        $processedResults = [];
        $position = 0;

        foreach ($results as $result) {
            $status = strtoupper($result['status'] ?? 'FINISHED');
            $driverId = $result['driver']['driverId'] ?? null;

            if (!$driverId) continue;
            
            // Skip non-participants
            if (in_array($status, ['DNS', 'DID NOT START', 'EXCLUDED', 'WITHDREW'])) {
                continue;
            }

            $processedResults[] = [
                'driverId' => $driverId,
                'position' => $position,
                'status' => in_array($status, ['FINISHED', '+1 LAP', '+2 LAPS']) ? 'FINISHED' : 'DNF',
                'fastestLap' => (bool)($result['fastestLap'] ?? false),
            ];
            $position++;
        }

        return $processedResults;
    }

    private function findDriverInProcessedResults(string $driverId, array $processedResults): ?array
    {
        foreach ($processedResults as $item) {
            if ($item['driverId'] === $driverId) {
                return $item;
            }
        }
        return null;
    }

    private function getPositionScore(int $positionDiff, int $season): int
    {
        return match ($positionDiff) {
            0 => 25,  // Spot on
            1 => 18,  // Very close
            2 => 15,
            3 => 12,
            4 => 10,
            5 => 8,
            6 => 6,
            7 => 4,
            8 => 2,
            9 => 1,
            default => 0,
        };
    }

    private function getMissingDriverScore(string $driverId, array $results, int $season): int
    {
        // If a driver you predicted didn't even start, you get a small penalty for poor research/luck
        return -5; 
    }

    private function calculateFastestLapScore(Prediction $prediction, array $processedResults): int
    {
        $predictedFL = $prediction->getPredictedFastestLap();
        if (!$predictedFL) return 0;

        foreach ($processedResults as $item) {
            if ($item['driverId'] === $predictedFL && $item['fastestLap']) {
                return 10;
            }
        }

        return 0;
    }

    public function savePredictionScore(Prediction $prediction, int $score): void
    {
        $prediction->update([
            'score' => $score,
            'accuracy' => $this->calculateAccuracyValue($prediction),
            'status' => 'scored',
            'scored_at' => now(),
        ]);
    }

    public function calculateAccuracyValue(Prediction $prediction): float
    {
        if ($prediction->type !== 'race') return 0.0;

        $predictedOrder = $prediction->getPredictedDriverOrder();
        $actualResults = $prediction->race->getResultsArray();
        
        if (empty($actualResults) || empty($predictedOrder)) return 0.0;

        $correctCount = 0;
        $total = count($predictedOrder);

        $processed = $this->processRaceResults($actualResults);

        foreach ($predictedOrder as $pos => $driverId) {
            $actual = $this->findDriverInProcessedResults($driverId, $processed);
            if ($actual !== null && $actual['position'] === $pos) {
                $correctCount++;
            }
        }

        return ($total > 0) ? ($correctCount / $total) * 100 : 0.0;
    }
}
