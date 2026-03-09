<?php

namespace App\Services\Scoring;

use App\Models\Prediction;
use App\Models\Races;

class RaceScoringService
{
    public function calculateRaceScore(Prediction $prediction, Races $race): int
    {
        if ($prediction->type !== 'race') {
            return 0;
        }

        $predictedOrder = $prediction->getPredictedDriverOrder();
        $actualResults = $this->processRaceResults($race->getResultsArray());

        if ($actualResults === []) {
            return 0;
        }

        $score = 0;
        $predictedOrder = array_filter($predictedOrder, fn ($id) => $id !== null && $id !== '');
        $totalDrivers = count($predictedOrder);
        $correctCount = 0;

        foreach ($predictedOrder as $position => $driverId) {
            $actualPosition = $this->findDriverPosition($driverId, $actualResults);

            if ($actualPosition !== null) {
                $positionDiff = abs($position - $actualPosition);
                $positionScore = $this->getPositionScore($positionDiff, $race->season);
                $score += $positionScore;

                if ($positionDiff === 0) {
                    $correctCount++;
                }
            } else {
                $score += $this->getMissingDriverScore($driverId, $actualResults, $race->season);
            }
        }

        $score += $this->calculateFastestLapScore($prediction, $actualResults);
        $score += $this->calculateDnfWagerScore($prediction, $race);

        if ($totalDrivers > 0 && $correctCount === $totalDrivers) {
            $score += 50;
        }

        if ($race->half_points ?? false) {
            $score = (int) round($score / 2);
        }

        return $score;
    }

    public function calculateSprintScore(Prediction $prediction, Races $race): int
    {
        if ($prediction->type !== 'sprint') {
            return 0;
        }

        $predictedOrder = $prediction->getPredictedDriverOrder();
        $predictedOrder = array_filter($predictedOrder, fn ($id) => $id !== null && $id !== '');
        $actualResults = $this->processRaceResults($race->getResultsArray());

        if ($actualResults === []) {
            return 0;
        }

        $score = 0;
        $top8Correct = 0;

        foreach ($predictedOrder as $position => $driverId) {
            $actualPosition = $this->findDriverPosition($driverId, $actualResults);

            if ($actualPosition !== null) {
                $positionDiff = abs($position - $actualPosition);
                $positionScore = $this->getSprintPositionScore($positionDiff, $race->season);
                $score += $positionScore;

                if ($positionDiff === 0 && $position < 8) {
                    $top8Correct++;
                }
            } else {
                $score += $this->getMissingDriverScore($driverId, $actualResults, $race->season);
            }
        }

        $score += $this->calculateFastestLapScore($prediction, $actualResults);

        if ($top8Correct >= 8) {
            $score += 15;
        }

        if ($race->half_points ?? false) {
            $score = (int) round($score / 2);
        }

        return $score;
    }

    /**
     * @return array{total: int, half_points: bool, fastest_lap_row: array{predicted_driver_id: string|null, actual_driver_id: string|null, points: int}, driver_rows: list<array{position: int, predicted_driver_id: string, actual_display: string, diff: int|null, points: int}>, dnf_wager_points: int, perfect_bonus: int}
     */
    public function buildBreakdown(Prediction $prediction, Races $race): array
    {
        $empty = [
            'total' => (int) $prediction->score,
            'half_points' => (bool) ($race->half_points ?? false),
            'fastest_lap_row' => ['predicted_driver_id' => null, 'actual_driver_id' => null, 'points' => 0],
            'driver_rows' => [],
            'dnf_wager_points' => 0,
            'perfect_bonus' => 0,
        ];

        if (! in_array($prediction->type, ['race', 'sprint'], true) || ! $race->isCompleted()) {
            return $empty;
        }

        $rawResults = $race->getResultsArray();
        $processedResults = $this->processRaceResults($rawResults);

        $driverIdToRawStatus = [];
        foreach ($rawResults as $result) {
            $driver = $this->extractDriverData($result);
            if ($driver && isset($driver['driverId'])) {
                $status = $result['status'] ?? '';
                $driverIdToRawStatus[(string) $driver['driverId']] = strtoupper((string) $status) ?: 'N/A';
            }
        }

        $predictedOrder = $prediction->getPredictedDriverOrder();
        $predictedOrder = array_filter($predictedOrder, fn ($id) => $id !== null && $id !== '');
        $predictedOrder = array_values($predictedOrder);

        $isSprint = $prediction->type === 'sprint';
        $driverRows = [];
        $correctCount = 0;
        $top8Correct = 0;

        foreach ($predictedOrder as $position => $driverId) {
            $position1Based = $position + 1;
            $actualPosition = $this->findDriverPosition((string) $driverId, $processedResults);

            if ($actualPosition !== null) {
                $actualDisplay = (string) ($actualPosition + 1);
                $diff = $actualPosition - $position;
                $positionDiff = abs($diff);
                $points = $isSprint
                    ? $this->getSprintPositionScore($positionDiff, $race->season)
                    : $this->getPositionScore($positionDiff, $race->season);

                if ($positionDiff === 0) {
                    $correctCount++;
                    if ($position < 8) {
                        $top8Correct++;
                    }
                }
            } else {
                $actualDisplay = $driverIdToRawStatus[(string) $driverId] ?? 'N/A';
                $diff = null;
                $points = 0;
            }

            $driverRows[] = [
                'position' => $position1Based,
                'predicted_driver_id' => (string) $driverId,
                'actual_display' => $actualDisplay,
                'diff' => $diff,
                'points' => $points,
            ];
        }

        $actualFastestLapDriverId = null;
        foreach ($processedResults as $result) {
            if (($result['fastestLap'] ?? false) === true) {
                $actualFastestLapDriverId = $result['driver']['driverId'] ?? null;
                break;
            }
        }

        $predictedFastestLap = $prediction->getPredictedFastestLap();
        $fastestLapPoints = 0;

        if ($predictedFastestLap && $actualFastestLapDriverId && (string) $actualFastestLapDriverId === (string) $predictedFastestLap) {
            $fastestLapPoints = $isSprint ? 5 : 10;
        }

        $fastestLapRow = [
            'predicted_driver_id' => $predictedFastestLap ? (string) $predictedFastestLap : null,
            'actual_driver_id' => $actualFastestLapDriverId ? (string) $actualFastestLapDriverId : null,
            'points' => $fastestLapPoints,
        ];

        $dnfWagerPoints = $this->calculateDnfWagerScore($prediction, $race);
        $totalDrivers = count($predictedOrder);
        $perfectBonus = 0;

        if ($isSprint && $top8Correct >= 8) {
            $perfectBonus = 15;
        } elseif (! $isSprint && $totalDrivers > 0 && $correctCount === $totalDrivers) {
            $perfectBonus = 50;
        }

        $total = (int) $prediction->score;
        $halfPoints = (bool) ($race->half_points ?? false);

        return [
            'total' => $total,
            'half_points' => $halfPoints,
            'fastest_lap_row' => $fastestLapRow,
            'driver_rows' => $driverRows,
            'dnf_wager_points' => $dnfWagerPoints,
            'perfect_bonus' => $perfectBonus,
        ];
    }

    /**
     * @return list<array{driver: array, position: int, status: string, points: int, fastestLap: bool}>
     */
    private function processRaceResults(array $results): array
    {
        $processedResults = [];
        $position = 0;

        foreach ($results as $result) {
            $status = $result['status'] ?? 'finished';
            $driver = $this->extractDriverData($result);

            switch (strtoupper($status)) {
                case 'FINISHED':
                case 'DNF':
                    if (! $driver) {
                        break;
                    }
                    $processedResults[] = [
                        'driver' => $driver,
                        'position' => $position,
                        'status' => $status,
                        'points' => $result['points'] ?? 0,
                        'fastestLap' => $this->extractFastestLapFlag($result),
                    ];
                    $position++;
                    break;

                case 'DNS':
                case 'DSQ':
                case 'EXCLUDED':
                    break;

                default:
                    if (! $driver) {
                        break;
                    }
                    $processedResults[] = [
                        'driver' => $driver,
                        'position' => $position,
                        'status' => $status,
                        'points' => $result['points'] ?? 0,
                        'fastestLap' => $this->extractFastestLapFlag($result),
                    ];
                    $position++;
                    break;
            }
        }

        return $processedResults;
    }

    private function findDriverPosition(string $driverId, array $processedResults): ?int
    {
        foreach ($processedResults as $result) {
            $rid = $result['driver']['driverId'] ?? '';
            if ((string) $rid === (string) $driverId) {
                return $result['position'];
            }
        }

        return null;
    }

    private function getPositionScore(int $positionDiff, int $season): int
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
            10 => 0,
            11 => -1,
            12 => -2,
            13 => -4,
            14 => -6,
            15 => -8,
            16 => -10,
            17 => -12,
            18 => -15,
            19 => -18,
            default => -25,
        };
    }

    private function getSprintPositionScore(int $positionDiff, int $season): int
    {
        return match ($positionDiff) {
            0 => 8,
            1 => 7,
            2 => 6,
            3 => 5,
            4 => 4,
            5 => 3,
            6 => 2,
            7 => 1,
            default => 0,
        };
    }

    private function getMissingDriverScore(string $driverId, array $results, int $season): int
    {
        return 0;
    }

    private function calculateFastestLapScore(Prediction $prediction, array $processedResults): int
    {
        $predictedFL = $prediction->getPredictedFastestLap();

        if (! $predictedFL) {
            return 0;
        }

        foreach ($processedResults as $result) {
            if (($result['fastestLap'] ?? false) === true) {
                $actual = $result['driver']['driverId'] ?? null;
                if ($actual && (string) $actual === (string) $predictedFL) {
                    return $prediction->type === 'sprint' ? 5 : 10;
                }
                break;
            }
        }

        return 0;
    }

    private function calculateDnfWagerScore(Prediction $prediction, Races $race): int
    {
        if ($prediction->type !== 'race') {
            return 0;
        }

        $predictedDnf = $prediction->getDnfPredictions();
        if ($predictedDnf === []) {
            return 0;
        }

        $actualDnf = $this->getActualDnfDriverIds($race->getResultsArray());
        $score = 0;

        foreach ($predictedDnf as $driverId) {
            $driverIdStr = (string) $driverId;
            if (in_array($driverIdStr, $actualDnf, true)) {
                $score += 10;
            } else {
                $score -= 10;
            }
        }

        return $score;
    }

    /**
     * @return list<string>
     */
    private function getActualDnfDriverIds(array $results): array
    {
        $ids = [];

        foreach ($results as $result) {
            $status = strtoupper((string) ($result['status'] ?? ''));
            if ($status !== 'DNF') {
                continue;
            }

            $driver = $this->extractDriverData($result);
            if ($driver && isset($driver['driverId'])) {
                $ids[] = (string) $driver['driverId'];
            }
        }

        return $ids;
    }

    /**
     * Normalize either nested-driver or flat-driver result rows.
     *
     * @return array{driverId: string, name?: string}|null
     */
    private function extractDriverData(array $result): ?array
    {
        $driver = $result['driver'] ?? null;
        if (is_array($driver) && isset($driver['driverId'])) {
            return $driver;
        }

        $driverId = $result['driverId'] ?? $result['driver_id'] ?? null;
        if (! is_string($driverId) || $driverId === '') {
            if (is_array($driver)) {
                $nestedDriverId = $driver['driverId'] ?? $driver['driver_id'] ?? null;
                if (is_string($nestedDriverId) && $nestedDriverId !== '') {
                    $driverId = $nestedDriverId;
                }
            }
        }

        if (! is_string($driverId) || $driverId === '') {
            return null;
        }

        $name = null;
        if (is_string($driver)) {
            $name = $driver;
        } elseif (isset($result['name']) && is_string($result['name'])) {
            $name = $result['name'];
        }

        return array_filter([
            'driverId' => $driverId,
            'name' => $name,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function extractFastestLapFlag(array $result): bool
    {
        return (bool) ($result['fastestLap'] ?? $result['fastest_lap'] ?? false);
    }
}
