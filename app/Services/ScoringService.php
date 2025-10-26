<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Notifications\PredictionScored;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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
                
                // Send notification
                $prediction->user->notify(new PredictionScored($prediction, $score, $prediction->accuracy));
                
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
        $correctPredictions = 0;

        foreach ($predictedOrder as $position => $driverId) {
            $actualPosition = $this->findDriverPosition($driverId, $actualResults);
            
            if ($actualPosition !== null) {
                $positionDiff = abs($position - $actualPosition);
                $positionScore = $this->getPositionScore($positionDiff, $race->season);
                $score += $positionScore;
                
                if ($positionDiff === 0) {
                    $correctPredictions++;
                }
            } else {
                // Driver not found in results (DNS, DSQ, etc.)
                $score += $this->getMissingDriverScore($driverId, $actualResults, $race->season);
            }
        }

        // Add fastest lap bonus if implemented
        $fastestLapScore = $this->calculateFastestLapScore($prediction, $actualResults);
        $score += $fastestLapScore;

        // Add perfect prediction bonus
        if ($correctPredictions === $totalDrivers && $totalDrivers > 0) {
            $score += 50; // Perfect prediction bonus
        }

        return $score;
    }

    /**
     * Process race results to handle edge cases
     */
    private function processRaceResults(array $results): array
    {
        $processedResults = [];
        $position = 0;

        foreach ($results as $result) {
            $status = $result['status'] ?? 'finished';
            
            // Handle different status types
            switch (strtoupper($status)) {
                case 'FINISHED':
                case 'DNF': // Did Not Finish - still gets position
                    $processedResults[] = [
                        'driver' => $result['driver'],
                        'position' => $position,
                        'status' => $status,
                        'points' => $result['points'] ?? 0,
                        'fastestLap' => $result['fastestLap'] ?? false,
                    ];
                    $position++;
                    break;
                    
                case 'DNS': // Did Not Start - remove from results
                case 'DSQ': // Disqualified - remove from results
                case 'EXCLUDED':
                    // Skip these drivers entirely
                    break;
                    
                default:
                    // Unknown status, treat as finished
                    $processedResults[] = [
                        'driver' => $result['driver'],
                        'position' => $position,
                        'status' => $status,
                        'points' => $result['points'] ?? 0,
                        'fastestLap' => $result['fastestLap'] ?? false,
                    ];
                    $position++;
                    break;
            }
        }

        return $processedResults;
    }

    /**
     * Find driver position in processed results
     */
    public function findDriverPosition(string $driverId, array $results): ?int
    {
        foreach ($results as $result) {
            if (($result['driver']['driverId'] ?? '') === $driverId) {
                return $result['position'];
            }
        }
        return null;
    }

    /**
     * Get score for position difference (season-specific)
     */
    private function getPositionScore(int $positionDiff, int $season): int
    {
        // Base scoring system (can be modified per season)
        return match ($positionDiff) {
            0 => 25,  // Correct prediction
            1 => 18,  // 1 position away
            2 => 15,  // 2 positions away
            3 => 12,  // 3 positions away
            4 => 10,  // 4 positions away
            5 => 8,   // 5 positions away
            6 => 6,   // 6 positions away
            7 => 4,   // 7 positions away
            8 => 2,   // 8 positions away
            9 => 1,   // 9 positions away
            default => max(-25, -$positionDiff), // 10+ positions away
        };
    }

    /**
     * Handle missing drivers (DNS, DSQ, etc.)
     */
    private function getMissingDriverScore(string $driverId, array $results, int $season): int
    {
        // Check if driver was DNS/DSQ in original results
        // For now, return 0 points for missing drivers
        // This could be enhanced to check original race data
        
        return 0; // DNS/DSQ drivers get 0 points
    }

    /**
     * Calculate fastest lap bonus (if implemented)
     */
    private function calculateFastestLapScore(Prediction $prediction, array $results): int
    {
        $predictedFastestLap = $prediction->getPredictedFastestLap();
        
        if (!$predictedFastestLap) {
            return 0;
        }

        // Find actual fastest lap from results
        $actualFastestLap = null;
        foreach ($results as $result) {
            if (($result['fastestLap'] ?? false) === true) {
                $actualFastestLap = $result['driver']['driverId'] ?? null;
                break;
            }
        }

        if ($actualFastestLap && $actualFastestLap === $predictedFastestLap) {
            return 10; // Fastest lap bonus
        }

        return 0;
    }

    /**
     * Save prediction score to database
     */
    public function savePredictionScore(Prediction $prediction, int $score): void
    {
        $prediction->update([
            'score' => $score,
            'accuracy' => $this->calculateAccuracy($prediction),
            'status' => 'scored',
            'scored_at' => now(),
        ]);
    }

    /**
     * Calculate prediction accuracy
     */
    public function calculateAccuracy(Prediction $prediction): float
    {
        if ($prediction->type !== 'race') {
            return 0.0;
        }

        $predictedOrder = $prediction->getPredictedDriverOrder();
        $actualResults = $prediction->race->getResultsArray();
        
        if (empty($actualResults) || empty($predictedOrder)) {
            return 0.0;
        }

        $correctPredictions = 0;
        $totalPredictions = count($predictedOrder);

        foreach ($predictedOrder as $position => $driverId) {
            $actualPosition = $this->findDriverPosition($driverId, $actualResults);
            
            if ($actualPosition !== null && $position === $actualPosition) {
                $correctPredictions++;
            }
        }

        return ($correctPredictions / $totalPredictions) * 100;
    }

    /**
     * Admin override: manually set prediction score
     */
    public function overridePredictionScore(Prediction $prediction, int $score, string $reason = null): void
    {
        $prediction->update([
            'score' => $score,
            'accuracy' => $this->calculateAccuracy($prediction),
            'status' => 'scored',
            'scored_at' => now(),
            'notes' => $reason ? "Admin override: {$reason}" : 'Admin override',
        ]);

        // Send notification
        $prediction->user->notify(new PredictionScored($prediction, $score, $prediction->accuracy));
    }

    /**
     * Handle driver substitutions for a race
     */
    public function handleDriverSubstitutions(Races $race, array $substitutions): void
    {
        // Find predictions that include substituted drivers
        $predictions = $race->predictions()
            ->whereIn('status', ['submitted', 'locked'])
            ->get();

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
    public function handleRaceCancellation(Races $race, string $reason = null): void
    {
        $predictions = $race->predictions()
            ->whereIn('status', ['submitted', 'locked'])
            ->get();

        foreach ($predictions as $prediction) {
            $prediction->update([
                'status' => 'cancelled',
                'score' => 0,
                'accuracy' => 0,
                'notes' => $reason ? "Race cancelled: {$reason}" : 'Race cancelled',
            ]);
        }
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
