<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Services\ChartDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    /**
     * Show the main leaderboard.
     */
    public function index(Request $request): View
    {
        $season = $request->get('season', date('Y'));
        $type = $request->get('type', 'all');

        $leaderboard = $this->getLeaderboard($season, $type);

        $seasons = $this->getAvailableSeasons();
        $types = ['all' => 'All Predictions', 'race' => 'Race Predictions', 'preseason' => 'Preseason Predictions'];

        return view('leaderboard.index', compact('leaderboard', 'seasons', 'types', 'season', 'type'));
    }

    /**
     * Show season leaderboard.
     */
    public function season(int $season): View
    {
        $leaderboard = $this->getLeaderboard($season, 'all');
        $seasons = $this->getAvailableSeasons();

        return view('leaderboard.season', compact('leaderboard', 'seasons', 'season'));
    }

    /**
     * Show race-specific leaderboard.
     */
    public function race(int $season, int $raceRound): View
    {
        $leaderboard = $this->getRaceLeaderboard($season, $raceRound);
        $race = Races::where('season', $season)
            ->where('round', $raceRound)
            ->firstOrFail();

        return view('leaderboard.race', compact('leaderboard', 'race', 'season', 'raceRound'));
    }

    /**
     * Show head-to-head comparison for selected users (F1-012).
     * URL: /leaderboard/compare?season=2024&users=1,2,3 (shareable).
     */
    public function compare(Request $request): View
    {
        $season = (int) $request->get('season', date('Y'));
        $usersParam = $request->input('users', '');
        $userIds = is_array($usersParam)
            ? array_map('intval', array_filter($usersParam))
            : array_map('intval', array_filter(explode(',', (string) $usersParam)));

        $availableUsers = $this->getUsersWithPredictions($season);
        $comparisonData = [];
        $progressionData = [];

        if (! empty($userIds)) {
            $chartService = app(ChartDataService::class);
            $userIds = array_values(array_unique(array_intersect($userIds, $availableUsers->pluck('id')->toArray())));
            $comparisonData = $chartService->getHeadToHeadComparison($userIds, $season);
            $progressionData = $chartService->getHeadToHeadScoreProgression($userIds, $season);
        }

        $seasons = $this->getAvailableSeasons();

        return view('leaderboard.compare', compact(
            'season',
            'userIds',
            'availableUsers',
            'comparisonData',
            'progressionData',
            'seasons'
        ));
    }

    /**
     * Show user's personal statistics.
     */
    public function userStats(User $user): View
    {
        $stats = $this->getUserStats($user);
        $recentPredictions = $user->predictions()
            ->with('race')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('leaderboard.user-stats', compact('user', 'stats', 'recentPredictions'));
    }

    /**
     * Get leaderboard data.
     */
    private function getLeaderboard(int $season, string $type): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::withCount(['predictions' => function ($query) use ($season, $type) {
            $query->where('season', $season);
            if ($type !== 'all') {
                $query->where('type', $type);
            }
        }])
            ->withSum(['predictions as total_score' => function ($query) use ($season, $type) {
                $query->where('season', $season)
                    ->where('status', 'scored');
                if ($type !== 'all') {
                    $query->where('type', $type);
                }
            }], 'score')
            ->withAvg(['predictions as avg_score' => function ($query) use ($season, $type) {
                $query->where('season', $season)
                    ->where('status', 'scored');
                if ($type !== 'all') {
                    $query->where('type', $type);
                }
            }], 'score')
            ->whereHas('predictions', function ($query) use ($season, $type) {
                $query->where('season', $season);
                if ($type !== 'all') {
                    $query->where('type', $type);
                }
            })
            ->orderBy('total_score', 'desc')
            ->orderBy('avg_score', 'desc')
            ->get();

        // Add ranking
        $query->each(function ($user, $index) {
            $user->rank = $index + 1;
        });

        return $query;
    }

    /**
     * Get race-specific leaderboard.
     */
    private function getRaceLeaderboard(int $season, int $raceRound): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::with(['predictions' => function ($query) use ($season, $raceRound) {
            $query->where('season', $season)
                ->where('race_round', $raceRound)
                ->where('type', 'race');
        }])
            ->whereHas('predictions', function ($query) use ($season, $raceRound) {
                $query->where('season', $season)
                    ->where('race_round', $raceRound)
                    ->where('type', 'race');
            })
            ->get()
            ->map(function ($user) {
                $prediction = $user->predictions->first();
                $user->prediction = $prediction;
                $user->score = $prediction->score ?? 0;

                return $user;
            })
            ->sortByDesc('score')
            ->values();

        // Add ranking
        $query->each(function ($user, $index) {
            $user->rank = $index + 1;
        });

        return $query;
    }

    /**
     * Get user statistics.
     */
    private function getUserStats(User $user): array
    {
        $totalPredictions = $user->predictions()->count();
        $scoredPredictions = $user->predictions()->where('status', 'scored')->count();
        $totalScore = $user->predictions()->where('status', 'scored')->sum('score');
        $avgScore = $user->predictions()->where('status', 'scored')->avg('score') ?? 0;
        $bestScore = $user->predictions()->where('status', 'scored')->max('score') ?? 0;

        // Calculate accuracy (assuming 25 points is perfect)
        $accuracy = $scoredPredictions > 0 ? ($totalScore / ($scoredPredictions * 25)) * 100 : 0;

        // Season breakdown
        $seasonStats = $user->predictions()
            ->where('status', 'scored')
            ->selectRaw('season, COUNT(*) as predictions, SUM(score) as total_score, AVG(score) as avg_score')
            ->groupBy('season')
            ->orderBy('season', 'desc')
            ->get();

        return [
            'total_predictions' => $totalPredictions,
            'scored_predictions' => $scoredPredictions,
            'total_score' => $totalScore,
            'avg_score' => round($avgScore, 2),
            'best_score' => $bestScore,
            'accuracy' => round($accuracy, 2),
            'season_stats' => $seasonStats,
        ];
    }

    /**
     * Get users who have predictions for a season (for comparison selector).
     */
    private function getUsersWithPredictions(int $season): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('predictions', function ($query) use ($season) {
            $query->where('season', $season)->where('status', 'scored');
        })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Get available seasons.
     */
    private function getAvailableSeasons(): array
    {
        return Prediction::distinct()
            ->pluck('season')
            ->sort()
            ->reverse()
            ->values()
            ->toArray();
    }
}
