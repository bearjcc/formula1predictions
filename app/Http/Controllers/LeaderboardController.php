<?php

namespace App\Http\Controllers;

use App\Models\Races;
use App\Models\User;
use App\Services\ChartDataService;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __construct(
        private readonly LeaderboardService $leaderboardService,
    ) {}

    /**
     * Show the main leaderboard.
     */
    public function index(Request $request): View
    {
        return view('leaderboard.index', [
            'season' => (int) $request->get('season', config('f1.current_season')),
        ]);
    }

    /**
     * Show season leaderboard.
     */
    public function season(int $season): View
    {
        $leaderboard = $this->leaderboardService->seasonLeaderboard($season, 'all');
        $seasons = $this->leaderboardService->availableSeasons();

        return view('leaderboard.season', compact('leaderboard', 'seasons', 'season'));
    }

    /**
     * Show race-specific leaderboard.
     */
    public function race(int $season, int $raceRound): View
    {
        $leaderboard = $this->leaderboardService->raceLeaderboard($season, $raceRound);
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

        $availableUsers = $this->leaderboardService->usersWithPredictions($season);
        $comparisonData = [];
        $progressionData = [];

        if (! empty($userIds)) {
            $chartService = app(ChartDataService::class);
            $userIds = array_values(array_unique(array_intersect($userIds, $availableUsers->pluck('id')->toArray())));
            $comparisonData = $chartService->getHeadToHeadComparison($userIds, $season);
            $progressionData = $chartService->getHeadToHeadScoreProgression($userIds, $season);
        }

        $seasons = $this->leaderboardService->availableSeasons();

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
        $stats = $this->leaderboardService->userStats($user);
        $recentPredictions = $user->predictions()
            ->with('race')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('leaderboard.user-stats', compact('user', 'stats', 'recentPredictions'));
    }
}
