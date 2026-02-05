<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\Prediction;
use App\Models\Races;

/**
 * Test-only harness for running alternative scoring variants against historical predictions.
 * Does not persist to the database. Used for experiments and backtesting.
 *
 * @see TODO.md F1-009
 */
class BacktestScoringHarness
{
    /** @var callable(int, int): int */
    private $positionScoreFn;

    public function __construct(?callable $positionScoreFn = null)
    {
        $this->positionScoreFn = $positionScoreFn ?? fn (int $diff, int $season) => self::productionPositionScore($diff);
    }

    /**
     * Production scoring formula (matches ScoringService::getPositionScore).
     */
    public static function productionPositionScore(int $positionDiff): int
    {
        return match ($positionDiff) {
            0 => 25,
            1 => 18,
            2 => 15,
            3 => 12,
            4 => 10,
            5 => 8,
            6 => 6,
            7 => 4,
            8 => 2,
            9 => 1,
            default => max(-25, -$positionDiff),
        };
    }

    /**
     * Alternative: linear decay, no negative scores.
     */
    public static function linearPositionScore(int $positionDiff): int
    {
        return max(0, 25 - (2 * $positionDiff));
    }

    /**
     * Alternative: flatter distribution, emphasizes top 5.
     */
    public static function flatterPositionScore(int $positionDiff): int
    {
        return match ($positionDiff) {
            0 => 20,
            1 => 16,
            2 => 12,
            3 => 9,
            4 => 6,
            5 => 4,
            6, 7, 8, 9 => 2,
            default => 0,
        };
    }

    /**
     * Compute score for a prediction using the configured position-scoring variant.
     */
    public function computeScore(Prediction $prediction, Races $race): int
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
            $driverIdStr = (string) $driverId;
            $actualPosition = $this->findDriverPosition($driverIdStr, $actualResults);

            if ($actualPosition !== null) {
                $positionDiff = abs($position - $actualPosition);
                $positionScore = ($this->positionScoreFn)($positionDiff, $race->season);
                $score += $positionScore;

                if ($positionDiff === 0) {
                    $correctPredictions++;
                }
            }
        }

        $fastestLapScore = $this->calculateFastestLapScore($prediction, $actualResults);
        $score += $fastestLapScore;

        if ($correctPredictions === $totalDrivers && $totalDrivers > 0) {
            $score += 50;
        }

        return $score;
    }

    /**
     * Run backtest comparing two scoring variants on a set of predictions.
     *
     * @param  array<Prediction>  $predictions
     * @return array{production_scores: array<int, int>, alternative_scores: array<int, int>, score_deltas: array<int, int>, rank_changes: array<int, int>}
     */
    public function compareVariants(array $predictions, string $alternativeName = 'linear'): array
    {
        $productionHarness = new self(fn (int $d, int $s) => self::productionPositionScore($d));
        $altFn = $alternativeName === 'linear' ? [self::class, 'linearPositionScore'] : [self::class, 'flatterPositionScore'];
        $alternativeHarness = new self($altFn);

        $productionScores = [];
        $alternativeScores = [];

        foreach ($predictions as $p) {
            if ($p->type !== 'race' || ! $p->race) {
                continue;
            }
            $productionScores[$p->id] = $productionHarness->computeScore($p, $p->race);
            $alternativeScores[$p->id] = $alternativeHarness->computeScore($p, $p->race);
        }

        $scoreDeltas = [];
        foreach (array_keys($productionScores) as $id) {
            $scoreDeltas[$id] = ($alternativeScores[$id] ?? 0) - ($productionScores[$id] ?? 0);
        }

        $rankChanges = $this->computeRankChanges($productionScores, $alternativeScores);

        return [
            'production_scores' => $productionScores,
            'alternative_scores' => $alternativeScores,
            'score_deltas' => $scoreDeltas,
            'rank_changes' => $rankChanges,
        ];
    }

    /**
     * Compute rank change per prediction ID (positive = improved, negative = dropped).
     */
    private function computeRankChanges(array $productionScores, array $alternativeScores): array
    {
        $prodRanks = $this->scoresToRanks($productionScores);
        $altRanks = $this->scoresToRanks($alternativeScores);

        $changes = [];
        foreach (array_keys($prodRanks) as $id) {
            $changes[$id] = ($prodRanks[$id] ?? 0) - ($altRanks[$id] ?? 0);
        }

        return $changes;
    }

    /**
     * @param  array<int, int>  $scores
     * @return array<int, int> prediction_id => rank (1 = best)
     */
    private function scoresToRanks(array $scores): array
    {
        $sorted = $scores;
        arsort($sorted, SORT_NUMERIC);

        $ranks = [];
        $rank = 1;
        foreach (array_keys($sorted) as $id) {
            $ranks[$id] = $rank++;
        }

        return $ranks;
    }

    private function processRaceResults(array $results): array
    {
        $processed = [];
        $position = 0;

        foreach ($results as $result) {
            $status = $result['status'] ?? 'finished';

            switch (strtoupper($status)) {
                case 'FINISHED':
                case 'DNF':
                    $processed[] = [
                        'driver' => $result['driver'],
                        'position' => $position,
                        'status' => $status,
                        'fastestLap' => $result['fastestLap'] ?? false,
                    ];
                    $position++;
                    break;
                case 'DNS':
                case 'DSQ':
                case 'EXCLUDED':
                    break;
                default:
                    $processed[] = [
                        'driver' => $result['driver'],
                        'position' => $position,
                        'status' => $status,
                        'fastestLap' => $result['fastestLap'] ?? false,
                    ];
                    $position++;
                    break;
            }
        }

        return $processed;
    }

    private function findDriverPosition(string $driverId, array $results): ?int
    {
        foreach ($results as $index => $result) {
            $rid = $result['driver']['driverId'] ?? '';
            if ((string) $rid === $driverId) {
                return $result['position'] ?? $index;
            }
        }

        return null;
    }

    private function calculateFastestLapScore(Prediction $prediction, array $results): int
    {
        $predicted = $prediction->getPredictedFastestLap();
        if (! $predicted) {
            return 0;
        }

        foreach ($results as $result) {
            if (($result['fastestLap'] ?? false) === true) {
                $actual = $result['driver']['driverId'] ?? null;
                if ($actual && (string) $actual === (string) $predicted) {
                    return 10;
                }
                break;
            }
        }

        return 0;
    }
}
