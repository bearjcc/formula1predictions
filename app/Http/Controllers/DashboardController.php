<?php

namespace App\Http\Controllers;

use App\Models\Races;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly LeaderboardService $leaderboardService,
    ) {}

    /**
     * Show the user's personalized dashboard.
     */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $season = config('f1.current_season');

        $stats = $this->getUserStats($user, $season);
        $upcomingRaces = $this->getUpcomingRaces($season);
        $leaderboard = $this->leaderboardService->topForSeason($season, 5);
        $recentPredictions = $this->getRecentPredictions($user, 5);
        $userRank = $this->leaderboardService->userRankForSeason($user, $season);
        $preseasonDeadline = Races::getPreseasonDeadlineForSeason($season);
        $firstRace = Races::getFirstRaceOfSeason($season);
        $hasPreseasonPrediction = $user->predictions()
            ->where('season', $season)
            ->where('type', 'preseason')
            ->exists();

        return view('dashboard', compact(
            'stats',
            'upcomingRaces',
            'leaderboard',
            'recentPredictions',
            'userRank',
            'season',
            'preseasonDeadline',
            'firstRace',
            'hasPreseasonPrediction'
        ));
    }

    /**
     * @return array{total_predictions: int, scored_predictions: int, total_score: int, avg_score: float, accuracy: float}
     */
    private function getUserStats(User $user, int $season): array
    {
        $totalPredictions = $user->predictions()->where('season', $season)->count();
        $scoredPredictions = $user->predictions()->where('season', $season)->where('status', 'scored')->count();
        $totalScore = (int) ($user->predictions()->where('season', $season)->where('status', 'scored')->sum('score') ?? 0);
        $avgScore = round($user->predictions()->where('season', $season)->where('status', 'scored')->avg('score') ?? 0, 1);

        $accuracy = $scoredPredictions > 0
            ? min(100, round(($totalScore / ($scoredPredictions * 25)) * 100, 1))
            : 0;

        return [
            'total_predictions' => $totalPredictions,
            'scored_predictions' => $scoredPredictions,
            'total_score' => $totalScore,
            'avg_score' => $avgScore,
            'accuracy' => $accuracy,
        ];
    }

    private function getUpcomingRaces(int $season, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Races::forSeason($season)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->take($limit)
            ->get();
    }

    private function getRecentPredictions(User $user, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $user->predictions()
            ->with('race')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }
}
