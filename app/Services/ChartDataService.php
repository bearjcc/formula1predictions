<?php

namespace App\Services;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
use App\Models\User;

class ChartDataService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get driver standings progression data for a specific season.
     */
    public function getDriverStandingsProgression(int $season): array
    {
        $races = Races::where('season', $season)
            ->whereNotNull('results')
            ->orderBy('round')
            ->get();

        $drivers = Drivers::all()->keyBy('id');
        $chartData = [];

        foreach ($races as $race) {
            $results = $race->getResultsArray();
            $raceData = [
                'race' => $race->race_name,
                'round' => $race->round,
                'date' => $race->date->format('M j'),
            ];

            foreach ($results as $position => $driverData) {
                $driverId = $driverData['driver_id'] ?? null;
                if ($driverId && isset($drivers[$driverId])) {
                    $driverName = $drivers[$driverId]->name.' '.$drivers[$driverId]->surname;
                    $raceData[$driverName] = $position + 1; // Convert to 1-based position
                }
            }

            $chartData[] = $raceData;
        }

        return $chartData;
    }

    /**
     * Get team standings progression data for a specific season.
     */
    public function getTeamStandingsProgression(int $season): array
    {
        $races = Races::where('season', $season)
            ->whereNotNull('results')
            ->orderBy('round')
            ->get();

        $drivers = Drivers::with('team')->get()->keyBy('id');
        $chartData = [];

        foreach ($races as $race) {
            $results = $race->getResultsArray();
            $raceData = [
                'race' => $race->race_name,
                'round' => $race->round,
                'date' => $race->date->format('M j'),
            ];

            $teamPoints = [];
            foreach ($results as $position => $driverData) {
                $driverId = $driverData['driver_id'] ?? null;
                if ($driverId && isset($drivers[$driverId])) {
                    $driver = $drivers[$driverId];
                    if ($driver->team) {
                        $teamName = $driver->team->team_name;
                        $points = $this->calculatePoints($position);
                        $teamPoints[$teamName] = ($teamPoints[$teamName] ?? 0) + $points;
                    }
                }
            }

            // Sort teams by points and assign positions
            arsort($teamPoints);
            $position = 1;
            foreach ($teamPoints as $teamName => $points) {
                $raceData[$teamName] = $position;
                $position++;
            }

            $chartData[] = $raceData;
        }

        return $chartData;
    }

    /**
     * Get prediction accuracy trends for a user.
     */
    public function getUserPredictionAccuracyTrends(User $user, int $season): array
    {
        $predictions = $user->predictions()
            ->where('season', $season)
            ->whereNotNull('accuracy')
            ->orderBy('submitted_at')
            ->get();

        $chartData = [];
        foreach ($predictions as $prediction) {
            $chartData[] = [
                'prediction' => $prediction->type.' #'.$prediction->id,
                'date' => $prediction->submitted_at->format('M j'),
                'accuracy' => round($prediction->accuracy, 1),
                'score' => $prediction->score,
                'type' => $prediction->type,
            ];
        }

        return $chartData;
    }

    /**
     * Get prediction accuracy comparison between users.
     */
    public function getPredictionAccuracyComparison(int $season): array
    {
        $users = User::with(['predictions' => function ($query) use ($season) {
            $query->where('season', $season)
                ->whereNotNull('accuracy');
        }])->get();

        $chartData = [];
        foreach ($users as $user) {
            if ($user->predictions->isNotEmpty()) {
                $avgAccuracy = $user->predictions->avg('accuracy');
                $totalScore = $user->predictions->sum('score');
                $totalPredictions = $user->predictions->count();

                $chartData[] = [
                    'user' => $user->name,
                    'avg_accuracy' => round($avgAccuracy, 1),
                    'total_score' => $totalScore,
                    'total_predictions' => $totalPredictions,
                ];
            }
        }

        // Sort by average accuracy descending
        usort($chartData, fn ($a, $b) => $b['avg_accuracy'] <=> $a['avg_accuracy']);

        return $chartData;
    }

    /**
     * Get race prediction accuracy by race.
     */
    public function getRacePredictionAccuracyByRace(int $season): array
    {
        $races = Races::where('season', $season)
            ->whereNotNull('results')
            ->orderBy('round')
            ->get();

        $chartData = [];
        foreach ($races as $race) {
            $predictions = Prediction::where('race_id', $race->id)
                ->where('type', 'race')
                ->whereNotNull('accuracy')
                ->get();

            if ($predictions->isNotEmpty()) {
                $avgAccuracy = $predictions->avg('accuracy');
                $totalPredictions = $predictions->count();

                $chartData[] = [
                    'race' => $race->race_name,
                    'round' => $race->round,
                    'avg_accuracy' => round($avgAccuracy, 1),
                    'total_predictions' => $totalPredictions,
                    'date' => $race->date->format('M j'),
                ];
            }
        }

        return $chartData;
    }

    /**
     * Get driver performance comparison.
     */
    public function getDriverPerformanceComparison(int $season): array
    {
        $standings = Standings::where('season', $season)
            ->where('type', 'drivers')
            ->whereNull('round') // Current standings
            ->get();

        $driverIds = $standings->pluck('entity_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $drivers = Drivers::with('team')
            ->whereIn('id', $driverIds)
            ->get()
            ->keyBy('id');

        $chartData = [];
        foreach ($standings as $standing) {
            $driverId = (int) $standing->entity_id;
            if ($driverId && isset($drivers[$driverId])) {
                $driver = $drivers[$driverId];
                $chartData[] = [
                    'driver' => $driver->name.' '.$driver->surname,
                    'team' => $driver->team?->team_name ?? 'Unknown',
                    'points' => $standing->points,
                    'position' => $standing->position,
                    'wins' => $standing->wins ?? 0,
                    'podiums' => $standing->podiums ?? 0,
                ];
            }
        }

        // Sort by points descending
        usort($chartData, fn ($a, $b) => $b['points'] <=> $a['points']);

        return $chartData;
    }

    /**
     * Get team performance comparison.
     */
    public function getTeamPerformanceComparison(int $season): array
    {
        $standings = Standings::where('season', $season)
            ->where('type', 'constructors')
            ->whereNull('round') // Current standings
            ->get();

        $teamIds = $standings->pluck('entity_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $teams = Teams::whereIn('id', $teamIds)
            ->get()
            ->keyBy('id');

        $chartData = [];
        foreach ($standings as $standing) {
            $teamId = (int) $standing->entity_id;
            if ($teamId && isset($teams[$teamId])) {
                $team = $teams[$teamId];
                $chartData[] = [
                    'team' => $team->team_name,
                    'points' => $standing->points,
                    'position' => $standing->position,
                    'wins' => $standing->wins ?? 0,
                    'podiums' => $standing->podiums ?? 0,
                ];
            }
        }

        // Sort by points descending
        usort($chartData, fn ($a, $b) => $b['points'] <=> $a['points']);

        return $chartData;
    }

    /**
     * Get driver points progression over races.
     */
    public function getDriverPointsProgression(int $season): array
    {
        $races = Races::where('season', $season)
            ->whereNotNull('results')
            ->orderBy('round')
            ->get();

        $drivers = Drivers::all()->keyBy('id');
        $driverPoints = [];
        $chartData = [];

        foreach ($races as $race) {
            $results = $race->getResultsArray();
            $raceData = [
                'race' => $race->race_name,
                'round' => $race->round,
                'date' => $race->date->format('M j'),
            ];

            // Calculate cumulative points for each driver
            foreach ($results as $position => $driverData) {
                $driverId = $driverData['driver_id'] ?? null;
                if ($driverId && isset($drivers[$driverId])) {
                    $driverName = $drivers[$driverId]->name.' '.$drivers[$driverId]->surname;
                    $points = $this->calculatePoints($position);
                    $driverPoints[$driverName] = ($driverPoints[$driverName] ?? 0) + $points;
                    $raceData[$driverName] = $driverPoints[$driverName];
                }
            }

            $chartData[] = $raceData;
        }

        return $chartData;
    }

    /**
     * Get team points progression over races.
     */
    public function getTeamPointsProgression(int $season): array
    {
        $races = Races::where('season', $season)
            ->whereNotNull('results')
            ->orderBy('round')
            ->get();

        $drivers = Drivers::with('team')->get()->keyBy('id');
        $teamPoints = [];
        $chartData = [];

        foreach ($races as $race) {
            $results = $race->getResultsArray();
            $raceData = [
                'race' => $race->race_name,
                'round' => $race->round,
                'date' => $race->date->format('M j'),
            ];

            foreach ($results as $position => $driverData) {
                $driverId = $driverData['driver_id'] ?? null;
                if ($driverId && isset($drivers[$driverId])) {
                    $driver = $drivers[$driverId];
                    if ($driver->team) {
                        $teamName = $driver->team->team_name;
                        $points = $this->calculatePoints($position);
                        $teamPoints[$teamName] = ($teamPoints[$teamName] ?? 0) + $points;
                        $raceData[$teamName] = $teamPoints[$teamName];
                    }
                }
            }

            $chartData[] = $raceData;
        }

        return $chartData;
    }

    /**
     * Get prediction accuracy by prediction type.
     */
    public function getPredictionAccuracyByType(int $season): array
    {
        $predictions = Prediction::where('season', $season)
            ->whereNotNull('accuracy')
            ->get()
            ->groupBy('type');

        $chartData = [];
        foreach ($predictions as $type => $typePredictions) {
            $avgAccuracy = $typePredictions->avg('accuracy');
            $totalPredictions = $typePredictions->count();
            $totalScore = $typePredictions->sum('score');

            $chartData[] = [
                'type' => ucfirst($type),
                'avg_accuracy' => round($avgAccuracy, 1),
                'total_predictions' => $totalPredictions,
                'total_score' => $totalScore,
                'avg_score' => round($totalScore / $totalPredictions, 1),
            ];
        }

        // Sort by average accuracy descending
        usort($chartData, fn ($a, $b) => $b['avg_accuracy'] <=> $a['avg_accuracy']);

        return $chartData;
    }

    /**
     * Get user performance trends over time.
     */
    public function getUserPerformanceTrends(User $user, int $season): array
    {
        $predictions = $user->predictions()
            ->where('season', $season)
            ->whereNotNull('accuracy')
            ->orderBy('submitted_at')
            ->get();

        $chartData = [];
        $cumulativeScore = 0;
        $cumulativeAccuracy = 0;
        $predictionCount = 0;

        foreach ($predictions as $prediction) {
            $cumulativeScore += $prediction->score;
            $cumulativeAccuracy += $prediction->accuracy;
            $predictionCount++;

            $chartData[] = [
                'prediction' => $prediction->type.' #'.$prediction->id,
                'date' => $prediction->submitted_at->format('M j'),
                'accuracy' => round($prediction->accuracy, 1),
                'score' => $prediction->score,
                'cumulative_score' => $cumulativeScore,
                'avg_accuracy' => round($cumulativeAccuracy / $predictionCount, 1),
                'type' => $prediction->type,
            ];
        }

        return $chartData;
    }

    /**
     * Get race result distribution (podiums, points, DNFs).
     */
    public function getRaceResultDistribution(int $season): array
    {
        $races = Races::where('season', $season)
            ->whereNotNull('results')
            ->orderBy('round')
            ->get();

        $chartData = [];
        foreach ($races as $race) {
            $results = $race->getResultsArray();
            $podiums = 0;
            $points = 0;
            $dnfs = 0;

            foreach ($results as $position => $driverData) {
                if ($position < 3) {
                    $podiums++;
                }
                if ($position < 10) {
                    $points++;
                }
                if (isset($driverData['status']) && in_array($driverData['status'], ['DNF', 'DNS', 'DQ'])) {
                    $dnfs++;
                }
            }

            $chartData[] = [
                'race' => $race->race_name,
                'round' => $race->round,
                'podiums' => $podiums,
                'points_finishers' => $points,
                'dnfs' => $dnfs,
                'date' => $race->date->format('M j'),
            ];
        }

        return $chartData;
    }

    /**
     * Get predictor luck and variance metrics for a season (experiment: F1-011).
     * Returns per-user: total_score, avg_accuracy, score_std_dev, expected_score, luck_index.
     * Does not alter leaderboards; for analytics/experiment only.
     *
     * @return array<int, array{user: string, total_score: int|float, avg_accuracy: float, prediction_count: int, score_std_dev: float|null, expected_score: float, luck_index: float}>
     */
    public function getPredictorLuckAndVariance(int $season): array
    {
        $predictions = Prediction::where('season', $season)
            ->whereNotNull('accuracy')
            ->whereNotNull('score')
            ->with('user:id,name')
            ->orderBy('user_id')
            ->get();

        $byUser = $predictions->groupBy('user_id');
        $chartData = [];

        foreach ($byUser as $userId => $userPredictions) {
            $user = $userPredictions->first()->user;
            if (! $user) {
                continue;
            }

            $scores = $userPredictions->pluck('score')->map(fn ($s) => (float) $s)->all();
            $totalScore = array_sum($scores);
            $count = count($scores);
            $avgAccuracy = $userPredictions->avg('accuracy');
            $expectedScore = $count > 0 ? ($avgAccuracy / 100) * 25 * $count : 0.0;
            $luckIndex = $totalScore - $expectedScore;

            $scoreStdDev = null;
            if ($count >= 2) {
                $mean = $totalScore / $count;
                $variance = array_sum(array_map(fn ($s) => ($s - $mean) ** 2, $scores)) / $count;
                $scoreStdDev = sqrt($variance);
            }

            $chartData[] = [
                'user' => $user->name,
                'total_score' => (int) round($totalScore),
                'avg_accuracy' => round((float) $avgAccuracy, 1),
                'prediction_count' => $count,
                'score_std_dev' => $scoreStdDev !== null ? round($scoreStdDev, 2) : null,
                'expected_score' => round($expectedScore, 1),
                'luck_index' => round($luckIndex, 1),
            ];
        }

        usort($chartData, fn ($a, $b) => $b['total_score'] <=> $a['total_score']);

        return $chartData;
    }

    /**
     * Get head-to-head comparison data for selected users in a season (F1-012).
     * Returns per-user total_score, avg_accuracy, prediction_count for comparison charts.
     *
     * @param  array<int>  $userIds
     * @return array<int, array{user: string, user_id: int, total_score: int|float, avg_accuracy: float, prediction_count: int}>
     */
    public function getHeadToHeadComparison(array $userIds, int $season): array
    {
        if (empty($userIds)) {
            return [];
        }

        $predictions = Prediction::where('season', $season)
            ->whereIn('user_id', $userIds)
            ->whereNotNull('accuracy')
            ->whereNotNull('score')
            ->with('user:id,name')
            ->get();

        $byUser = $predictions->groupBy('user_id');
        $chartData = [];

        foreach ($userIds as $userId) {
            $userPredictions = $byUser->get($userId);
            if (! $userPredictions || $userPredictions->isEmpty()) {
                continue;
            }

            $user = $userPredictions->first()->user;
            if (! $user) {
                continue;
            }

            $totalScore = $userPredictions->sum('score');
            $avgAccuracy = $userPredictions->avg('accuracy');
            $count = $userPredictions->count();

            $chartData[] = [
                'user' => $user->name,
                'user_id' => (int) $userId,
                'total_score' => (int) round($totalScore),
                'avg_accuracy' => round((float) $avgAccuracy, 1),
                'prediction_count' => $count,
            ];
        }

        usort($chartData, fn ($a, $b) => $b['total_score'] <=> $a['total_score']);

        return $chartData;
    }

    /**
     * Get head-to-head cumulative score progression by race for selected users (F1-012).
     * Used for line chart showing score progression over the season.
     *
     * @param  array<int>  $userIds
     * @return array<int, array<string, mixed>>
     */
    public function getHeadToHeadScoreProgression(array $userIds, int $season): array
    {
        if (empty($userIds)) {
            return [];
        }

        $races = Races::where('season', $season)
            ->whereNotNull('results')
            ->orderBy('round')
            ->get();

        $raceIds = $races->pluck('id')->all();
        $predictionsByRace = Prediction::whereIn('race_id', $raceIds)
            ->whereIn('user_id', $userIds)
            ->where('status', 'scored')
            ->whereNotNull('score')
            ->get()
            ->groupBy('race_id');

        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        $cumulativeScores = array_fill_keys($userIds, 0);
        $chartData = [];

        foreach ($races as $race) {
            $raceData = [
                'race' => $race->race_name,
                'round' => $race->round,
                'date' => $race->date->format('M j'),
            ];

            $predictions = $predictionsByRace->get($race->id, collect());
            foreach ($predictions as $prediction) {
                $cumulativeScores[$prediction->user_id] = ($cumulativeScores[$prediction->user_id] ?? 0) + $prediction->score;
            }

            foreach ($userIds as $userId) {
                $user = $users->get($userId);
                if ($user) {
                    $raceData[$user->name] = $cumulativeScores[$userId] ?? 0;
                }
            }

            $chartData[] = $raceData;
        }

        return $chartData;
    }

    /**
     * Get driver consistency analysis (standard deviation of positions).
     */
    public function getDriverConsistencyAnalysis(int $season): array
    {
        $races = Races::where('season', $season)
            ->whereNotNull('results')
            ->orderBy('round')
            ->get();

        $drivers = Drivers::all()->keyBy('id');
        $driverPositions = [];

        // Collect all positions for each driver
        foreach ($races as $race) {
            $results = $race->getResultsArray();
            foreach ($results as $position => $driverData) {
                $driverId = $driverData['driver_id'] ?? null;
                if ($driverId && isset($drivers[$driverId])) {
                    $driverName = $drivers[$driverId]->name.' '.$drivers[$driverId]->surname;
                    if (! isset($driverPositions[$driverName])) {
                        $driverPositions[$driverName] = [];
                    }
                    // Use the 'position' field from driverData, not the array key
                    $actualPosition = $driverData['position'] ?? $position;
                    $driverPositions[$driverName][] = $actualPosition + 1; // Convert to 1-based position
                }
            }
        }

        $chartData = [];
        foreach ($driverPositions as $driverName => $positions) {
            if (count($positions) > 1) {
                $avgPosition = array_sum($positions) / count($positions);
                $variance = array_sum(array_map(fn ($pos) => pow($pos - $avgPosition, 2), $positions)) / count($positions);
                $stdDev = sqrt($variance);

                $chartData[] = [
                    'driver' => $driverName,
                    'avg_position' => round($avgPosition, 1),
                    'std_deviation' => round($stdDev, 2),
                    'consistency_score' => round(100 - ($stdDev * 10), 1), // Higher is more consistent
                    'races' => count($positions),
                ];
            }
        }

        // Sort by consistency score descending
        usort($chartData, fn ($a, $b) => $b['consistency_score'] <=> $a['consistency_score']);

        return $chartData;
    }

    /**
     * Calculate points for a given position (F1 scoring system).
     */
    private function calculatePoints(int $position): int
    {
        return match ($position) {
            0 => 25, // 1st place
            1 => 18, // 2nd place
            2 => 15, // 3rd place
            3 => 12, // 4th place
            4 => 10, // 5th place
            5 => 8,  // 6th place
            6 => 6,  // 7th place
            7 => 4,  // 8th place
            8 => 2,  // 9th place
            9 => 1,  // 10th place
            default => 0,
        };
    }

    /**
     * Get chart configuration for different chart types.
     */
    public function getChartConfig(string $type, array $data): array
    {
        return match ($type) {
            'line' => $this->getLineChartConfig($data),
            'bar' => $this->getBarChartConfig($data),
            'radar' => $this->getRadarChartConfig($data),
            'doughnut' => $this->getDoughnutChartConfig($data),
            default => $this->getLineChartConfig($data),
        };
    }

    private function getLineChartConfig(array $data): array
    {
        return [
            'type' => 'line',
            'data' => $data,
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'reverse' => true, // Lower numbers (positions) at top
                    ],
                ],
                'plugins' => [
                    'legend' => [
                        'position' => 'top',
                    ],
                ],
            ],
        ];
    }

    private function getBarChartConfig(array $data): array
    {
        return [
            'type' => 'bar',
            'data' => $data,
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                    ],
                ],
                'plugins' => [
                    'legend' => [
                        'position' => 'top',
                    ],
                ],
            ],
        ];
    }

    private function getRadarChartConfig(array $data): array
    {
        return [
            'type' => 'radar',
            'data' => $data,
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'top',
                    ],
                ],
            ],
        ];
    }

    private function getDoughnutChartConfig(array $data): array
    {
        return [
            'type' => 'doughnut',
            'data' => $data,
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'top',
                    ],
                ],
            ],
        ];
    }
}
