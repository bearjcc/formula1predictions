<?php

namespace App\Services;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
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
                $results['errors'][] = "Prediction {$prediction->id}: ".$e->getMessage();
                Log::error("Failed to score prediction {$prediction->id}: ".$e->getMessage());
            }
        }

        return $results;
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
                $score = $this->calculateSprintPredictionScore($prediction, $race);
                $this->savePredictionScore($prediction, $score);

                $results['scored_predictions']++;
                $results['total_score'] += $score;

                $prediction->user->notify(new PredictionScored($prediction, $score, $prediction->accuracy));
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
        if ($prediction->type !== 'race') {
            return 0;
        }

        $predictedOrder = $prediction->getPredictedDriverOrder();
        $actualResults = $this->processRaceResults($race->getResultsArray());

        if (empty($actualResults)) {
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
                // Driver not in results (DNS, DSQ, etc.)
                $score += $this->getMissingDriverScore($driverId, $actualResults, $race->season);
            }
        }

        // Fastest lap bonus
        $fastestLapScore = $this->calculateFastestLapScore($prediction, $actualResults);
        $score += $fastestLapScore;

        // DNF wager: +10 per correct DNF prediction, -10 per incorrect (README)
        $score += $this->calculateDnfWagerScore($prediction, $race);

        // Perfect prediction bonus: +50 when every predicted driver is in the correct position (README)
        if ($totalDrivers > 0 && $correctCount === $totalDrivers) {
            $score += 50;
        }

        // Half points for shortened races (README)
        if ($race->half_points ?? false) {
            $score = (int) round($score / 2);
        }

        return $score;
    }

    /**
     * Calculate score for a sprint prediction.
     */
    public function calculateSprintPredictionScore(Prediction $prediction, Races $race): int
    {
        if ($prediction->type !== 'sprint') {
            return 0;
        }

        $predictedOrder = $prediction->getPredictedDriverOrder();
        $predictedOrder = array_filter($predictedOrder, fn ($id) => $id !== null && $id !== '');
        $actualResults = $this->processRaceResults($race->getResultsArray());

        if (empty($actualResults)) {
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

        $fastestLapScore = $this->calculateFastestLapScore($prediction, $actualResults);
        $score += $fastestLapScore;

        // Sprint perfect bonus: +15 when all top 8 positions predicted correctly
        if ($top8Correct >= 8) {
            $score += 15;
        }

        // Half points for shortened races (README)
        if ($race->half_points ?? false) {
            $score = (int) round($score / 2);
        }

        return $score;
    }

    /**
     * Calculate score for a preseason or midseason championship prediction.
     * Preseason: constructor order (preseason points table), teammate battles (+5 each), red flags/safety cars (if actuals set).
     * Midseason: team + driver order (race position table), +50 perfect bonus.
     */
    public function calculateChampionshipPredictionScore(Prediction $prediction, int $season): int
    {
        if (! in_array($prediction->type, ['preseason', 'midseason'], true)) {
            return 0;
        }

        $constructorStandings = Standings::getConstructorStandings($season, null);

        if ($prediction->type === 'preseason') {
            $score = $this->scorePreseasonConstructorOrder(
                $prediction->getConstructorOrder(),
                $constructorStandings
            );
            $score += $this->scoreTeammateBattles($prediction->getTeammateBattles(), $season);
            $actuals = config("f1.season_actuals.{$season}", []);
            $score += $this->scoreCountPrediction(
                $prediction->getRedFlags(),
                $actuals['red_flags'] ?? null
            );
            $score += $this->scoreCountPrediction(
                $prediction->getSafetyCars(),
                $actuals['safety_cars'] ?? null
            );

            return $score;
        }

        $driverStandings = Standings::getDriverStandings($season, null);

        if ($driverStandings->isEmpty() && $constructorStandings->isEmpty()) {
            return 0;
        }

        $driverLookup = Drivers::pluck('driver_id', 'id');
        $teamLookup = Teams::pluck('team_id', 'id');

        $driverScore = $this->scoreChampionshipOrder(
            $prediction->getDriverChampionshipOrder(),
            $driverStandings,
            fn (int $localId) => $driverLookup[$localId] ?? null
        );

        $teamScore = $this->scoreChampionshipOrder(
            $prediction->getConstructorOrder(),
            $constructorStandings,
            fn (int $localId) => $teamLookup[$localId] ?? null
        );

        $score = $driverScore['score'] + $teamScore['score'];
        $correctCount = $driverScore['correct'] + $teamScore['correct'];
        $totalPredicted = $driverScore['total'] + $teamScore['total'];

        if ($totalPredicted > 0 && $correctCount === $totalPredicted) {
            $score += 50;
        }

        return $score;
    }

    /**
     * Preseason constructor order: diff 0->10, 1->8, 2->6, 3->4, 4->2, 5->0, 6->-2, 7->-4, 8->-6, 9->-8, 10->-10, 10+ -> -10.
     */
    private function getPreseasonConstructorPositionScore(int $diff): int
    {
        return match (min($diff, 10)) {
            0 => 10,
            1 => 8,
            2 => 6,
            3 => 4,
            4 => 2,
            5 => 0,
            6 => -2,
            7 => -4,
            8 => -6,
            9 => -8,
            default => -10,
        };
    }

    /**
     * Score preseason constructor order using preseason points table.
     *
     * @param  array<int>  $predictedTeamIds
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\Standings>  $constructorStandings
     */
    private function scorePreseasonConstructorOrder(
        array $predictedTeamIds,
        \Illuminate\Database\Eloquent\Collection $constructorStandings
    ): int {
        $teamLookup = Teams::pluck('team_id', 'id');
        $entityToPosition = $constructorStandings->keyBy('entity_id')->map(fn ($s) => $s->position - 1)->all();
        $score = 0;

        foreach ($predictedTeamIds as $position => $localId) {
            $entityId = $teamLookup[$localId] ?? null;
            if ($entityId === null) {
                continue;
            }

            $actualPosition = $entityToPosition[$entityId] ?? null;
            if ($actualPosition === null) {
                continue;
            }

            $diff = abs($position - $actualPosition);
            $score += $this->getPreseasonConstructorPositionScore($diff);
        }

        return $score;
    }

    /**
     * Score teammate battles: +5 per correct prediction (which driver finishes higher in championship).
     *
     * @param  array<int, int>  $teammateBattles  team_id => driver_id (predicted to finish higher)
     */
    private function scoreTeammateBattles(array $teammateBattles, int $season): int
    {
        if (empty($teammateBattles)) {
            return 0;
        }

        $driverStandings = Standings::getDriverStandings($season, null);
        $driverIdToPosition = [];
        $driverLookup = Drivers::pluck('driver_id', 'id');
        foreach ($driverStandings as $row) {
            $driverIdToPosition[$row->entity_id] = $row->position;
        }

        $score = 0;
        $teams = Teams::whereIn('id', array_keys($teammateBattles))->with('drivers')->get();

        foreach ($teams as $team) {
            $predictedDriverId = $teammateBattles[$team->id] ?? null;
            if ($predictedDriverId === null) {
                continue;
            }

            $predictedEntityId = $driverLookup[$predictedDriverId] ?? null;
            if ($predictedEntityId === null) {
                continue;
            }

            $teammateIds = $team->drivers->pluck('id')->all();
            $teammateEntityIds = $team->drivers->pluck('driver_id', 'id')->all();
            $positions = [];
            foreach ($teammateEntityIds as $localId => $entityId) {
                $pos = $driverIdToPosition[$entityId] ?? null;
                if ($pos !== null) {
                    $positions[$localId] = $pos;
                }
            }

            if (count($positions) < 2) {
                continue;
            }

            $higherLocalId = array_search(min($positions), $positions, true);
            if ((int) $higherLocalId === (int) $predictedDriverId) {
                $score += 5;
            }
        }

        return $score;
    }

    /**
     * Score a count prediction (red flags or safety cars) by error: 0->15, 1->10, 2->5, 3+->0.
     * Returns 0 if actual is null (not set).
     */
    private function scoreCountPrediction(?int $predicted, ?int $actual): int
    {
        if ($actual === null || $predicted === null) {
            return 0;
        }

        $diff = abs($predicted - $actual);

        return match ($diff) {
            0 => 15,
            1 => 10,
            2 => 5,
            default => 0,
        };
    }

    /**
     * Score predicted order vs actual standings. Returns score and correct count.
     *
     * @param  array<int>  $predictedLocalIds  Predicted order (0-based positions)
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\Standings>  $actualStandings
     * @param  callable(int): ?string  $resolveToEntityId  Maps local ID to API entity_id
     * @return array{score: int, correct: int, total: int}
     */
    private function scoreChampionshipOrder(
        array $predictedLocalIds,
        \Illuminate\Database\Eloquent\Collection $actualStandings,
        callable $resolveToEntityId
    ): array {
        $entityToPosition = $actualStandings->keyBy('entity_id')->map(fn ($s) => $s->position - 1)->all();
        $score = 0;
        $correct = 0;

        foreach ($predictedLocalIds as $position => $localId) {
            $entityId = $resolveToEntityId($localId);
            if ($entityId === null) {
                continue;
            }

            $actualPosition = $entityToPosition[$entityId] ?? null;
            if ($actualPosition === null) {
                continue;
            }

            $diff = abs($position - $actualPosition);
            $score += $this->getPositionScore($diff, (int) date('Y'));
            if ($diff === 0) {
                $correct++;
            }
        }

        return ['score' => $score, 'correct' => $correct, 'total' => count($predictedLocalIds)];
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
                $score = $this->calculateChampionshipPredictionScore($prediction, $season);
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

    /**
     * Process race results to handle edge cases
     */
    private function processRaceResults(array $results): array
    {
        $processedResults = [];
        $position = 0;

        foreach ($results as $result) {
            $status = $result['status'] ?? 'finished';

            switch (strtoupper($status)) {
                case 'FINISHED':
                case 'DNF':
                    $driver = $result['driver'] ?? null;
                    if (! $driver) {
                        break;
                    }
                    $processedResults[] = [
                        'driver' => $driver,
                        'position' => $position,
                        'status' => $status,
                        'points' => $result['points'] ?? 0,
                        'fastestLap' => $result['fastestLap'] ?? false,
                    ];
                    $position++;
                    break;

                case 'DNS':
                case 'DSQ':
                case 'EXCLUDED':
                    break;

                default:
                    $driver = $result['driver'] ?? null;
                    if (! $driver) {
                        break;
                    }
                    $processedResults[] = [
                        'driver' => $driver,
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

    /**
     * Get score for position difference in sprint sessions.
     */
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

    /**
     * DNF wager: +10 per correct DNF prediction, -10 per incorrect. Race only.
     */
    private function calculateDnfWagerScore(Prediction $prediction, Races $race): int
    {
        if ($prediction->type !== 'race') {
            return 0;
        }

        $predictedDnf = $prediction->getDnfPredictions();
        if (empty($predictedDnf)) {
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
            $driver = $result['driver'] ?? null;
            if ($driver && isset($driver['driverId'])) {
                $ids[] = (string) $driver['driverId'];
            }
        }

        return $ids;
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
        if (! in_array($prediction->type, ['race', 'sprint'], true)) {
            return 0.0;
        }

        $predictedOrder = $prediction->getPredictedDriverOrder();
        $actualResults = $prediction->race->getResultsArray();

        if (empty($actualResults) || empty($predictedOrder)) {
            return 0.0;
        }

        $processed = $this->processRaceResults($actualResults);
        $correctCount = 0;
        $total = count($predictedOrder);

        foreach ($predictedOrder as $pos => $driverId) {
            $actualPosition = $this->findDriverPosition((string) $driverId, $processed);
            if ($actualPosition !== null && $actualPosition === $pos) {
                $correctCount++;
            }
        }

        return $total > 0 ? ($correctCount / $total) * 100 : 0.0;
    }

    /**
     * Get per-driver and fastest-lap breakdown for a scored race/sprint prediction (for display).
     *
     * @return array{total: int, half_points: bool, fastest_lap_row: array{predicted_driver_id: string|null, actual_driver_id: string|null, points: int}, driver_rows: list<array{position: int, predicted_driver_id: string, actual_display: string, diff: int|null, points: int}>, dnf_wager_points: int, perfect_bonus: int}
     */
    public function getPredictionBreakdown(Prediction $prediction, Races $race): array
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
            $driver = $result['driver'] ?? null;
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
    public function handleRaceCancellation(Races $race, ?string $reason = null): void
    {
        $predictions = $race->predictions()
            ->whereIn('status', ['submitted', 'locked'])
            ->get();

        foreach ($predictions as $prediction) {
            $prediction->forceFill([
                'status' => 'cancelled',
                'score' => 0,
                'accuracy' => 0,
                'notes' => $reason ? "Race cancelled: {$reason}" : 'Race cancelled',
            ])->save();
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
