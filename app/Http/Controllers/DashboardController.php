<?php

namespace App\Http\Controllers;

use App\Models\Races;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
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
        $leaderboard = $this->getLeaderboardTop($season, 5);
        $recentPredictions = $this->getRecentPredictions($user, 5);
        $userRank = $this->getUserRank($user, $season);
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

    private function getLeaderboardTop(int $season, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return User::withSum(['predictions as total_score' => function ($q) use ($season) {
            $q->where('season', $season)->where('status', 'scored');
        }], 'score')
            ->whereHas('predictions', fn ($q) => $q->where('season', $season))
            ->orderByDesc('total_score')
            ->take($limit)
            ->get(['id', 'name'])
            ->each(function ($u, $i) {
                $u->rank = $i + 1;
                $u->total_score = (int) ($u->total_score ?? 0);
            });
    }

    private function getRecentPredictions(User $user, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $user->predictions()
            ->with('race')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    private function getUserRank(User $user, int $season): ?int
    {
        $ranked = User::withSum(['predictions as total_score' => function ($q) use ($season) {
            $q->where('season', $season)->where('status', 'scored');
        }], 'score')
            ->whereHas('predictions', fn ($q) => $q->where('season', $season))
            ->orderByDesc('total_score')
            ->get(['id']);

        $pos = $ranked->search(fn ($u) => (int) $u->id === (int) $user->id);

        return $pos !== false ? $pos + 1 : null;
    }
}
