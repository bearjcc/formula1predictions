<?php

namespace App\Services;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Support\Collection;

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
            $results = $race->results ?? [];
            $raceData = [
                'race' => $race->race_name,
                'round' => $race->round,
                'date' => $race->date->format('M j'),
            ];

            foreach ($results as $position => $driverData) {
                $driverId = $driverData['driver_id'] ?? null;
                if ($driverId && isset($drivers[$driverId])) {
                    $driverName = $drivers[$driverId]->name . ' ' . $drivers[$driverId]->surname;
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

        $teams = Teams::all()->keyBy('id');
        $chartData = [];

        foreach ($races as $race) {
            $results = $race->results ?? [];
            $raceData = [
                'race' => $race->race_name,
                'round' => $race->round,
                'date' => $race->date->format('M j'),
            ];

            // Group results by team
            $teamPoints = [];
            foreach ($results as $position => $driverData) {
                $driverId = $driverData['driver_id'] ?? null;
                if ($driverId) {
                    $driver = Drivers::find($driverId);
                    if ($driver && $driver->team) {
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
                'prediction' => $prediction->type . ' #' . $prediction->id,
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
        usort($chartData, fn($a, $b) => $b['avg_accuracy'] <=> $a['avg_accuracy']);

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

        $chartData = [];
        foreach ($standings as $standing) {
            $driver = Drivers::find((int) $standing->entity_id);
            if ($driver) {
                $chartData[] = [
                    'driver' => $driver->name . ' ' . $driver->surname,
                    'team' => $driver->team?->team_name ?? 'Unknown',
                    'points' => $standing->points,
                    'position' => $standing->position,
                    'wins' => $standing->wins ?? 0,
                    'podiums' => $standing->podiums ?? 0,
                ];
            }
        }

        // Sort by points descending
        usort($chartData, fn($a, $b) => $b['points'] <=> $a['points']);

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

        $chartData = [];
        foreach ($standings as $standing) {
            $team = Teams::find((int) $standing->entity_id);
            if ($team) {
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
        usort($chartData, fn($a, $b) => $b['points'] <=> $a['points']);

        return $chartData;
    }

    /**
     * Calculate points for a given position (F1 scoring system).
     */
    private function calculatePoints(int $position): int
    {
        return match($position) {
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
        return match($type) {
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
