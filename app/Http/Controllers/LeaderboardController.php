<?php

namespace App\Http\Controllers;

use App\Models\Races;
use App\Models\User;
use App\Services\ChartDataService;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $season = (int) $request->get('season', date('Y'));
        $type = $request->get('type', 'all');

        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 20;

        $fullLeaderboard = $this->leaderboardService->seasonLeaderboard($season, $type);
        $total = $fullLeaderboard->count();

        $seasons = $this->leaderboardService->availableSeasons();
        $types = ['all' => 'All Predictions', 'race' => 'Race Predictions', 'preseason' => 'Preseason Predictions'];

        $showFocusView = false;
        $focusLeaderboard = collect();
        $userRank = null;

        $viewMode = $request->get('view', 'auto');

        $currentUser = $request->user();

        if ($currentUser !== null && $total > 0 && $viewMode !== 'full') {
            $currentUserId = (int) $currentUser->id;
            $userIndex = $fullLeaderboard->search(function ($user) use ($currentUserId) {
                return (int) $user->id === (int) $currentUserId;
            });

            if ($userIndex !== false) {
                $userRank = $userIndex + 1;

                if ($userRank > 8) {
                    $showFocusView = true;

                    $topFive = $fullLeaderboard->take(5);
                    $aroundStart = max(0, $userIndex - 1);
                    $around = $fullLeaderboard->slice($aroundStart, 3);

                    $focusLeaderboard = $topFive
                        ->concat($around)
                        ->unique('id')
                        ->values();
                }
            }
        }

        if ($showFocusView) {
            return view('leaderboard.index', [
                'leaderboard' => null,
                'focusLeaderboard' => $focusLeaderboard,
                'showFocusView' => true,
                'seasons' => $seasons,
                'types' => $types,
                'season' => $season,
                'type' => $type,
                'perPage' => $perPage,
                'userRank' => $userRank,
            ]);
        }

        $page = max(1, (int) $request->get('page', 1));
        $items = $fullLeaderboard->forPage($page, $perPage)->values();

        $leaderboard = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('leaderboard.index', [
            'leaderboard' => $leaderboard,
            'focusLeaderboard' => null,
            'showFocusView' => false,
            'seasons' => $seasons,
            'types' => $types,
            'season' => $season,
            'type' => $type,
            'perPage' => $perPage,
            'userRank' => $userRank,
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
