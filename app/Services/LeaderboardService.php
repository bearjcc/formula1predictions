<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\User;
use App\Services\Scoring\ChampionshipScoringService;
use App\Services\Scoring\RaceScoringService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class LeaderboardService
{
    public function __construct(
        private RaceScoringService $raceScoring,
        private ChampionshipScoringService $championshipScoring
    ) {}

    public function seasonLeaderboard(int $season, string $type = 'all'): Collection
    {
        $users = User::withCount(['predictions' => function ($query) use ($season, $type) {
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
            ->with(['predictions' => function ($query) use ($season, $type) {
                $query->where('season', $season)
                    ->where('status', 'scored');
                if ($type !== 'all') {
                    $query->where('type', $type);
                }
            }, 'predictions.race'])
            ->whereHas('predictions', function ($query) use ($season, $type) {
                $query->where('season', $season);
                if ($type !== 'all') {
                    $query->where('type', $type);
                }
            })
            ->orderBy('total_score', 'desc')
            ->orderBy('avg_score', 'desc')
            ->get();

        $users->each(function (User $user, int $index): void {
            $user->rank = $index + 1;
            $user->perfect_predictions_count = $user->predictions
                ->filter(fn (Prediction $prediction) => $this->isPerfectPrediction($prediction))
                ->count();
        });

        return $users->toBase();
    }

    public function raceLeaderboard(int $season, int $raceRound): EloquentCollection
    {
        $users = User::with(['predictions' => function ($query) use ($season, $raceRound) {
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
            ->map(function (User $user) {
                $prediction = $user->predictions->first();
                $user->prediction = $prediction;
                $user->score = $prediction->score ?? 0;

                return $user;
            })
            ->sortByDesc('score')
            ->values();

        $users->each(function (User $user, int $index): void {
            $user->rank = $index + 1;
        });

        return $users;
    }

    public function userStats(User $user): array
    {
        $totalPredictions = $user->predictions()->count();
        $scoredPredictions = $user->predictions()->where('status', 'scored')->count();
        $totalScore = $user->predictions()->where('status', 'scored')->sum('score');
        $avgScore = $user->predictions()->where('status', 'scored')->avg('score') ?? 0;
        $bestScore = $user->predictions()->where('status', 'scored')->max('score') ?? 0;

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
            'season_stats' => $seasonStats,
        ];
    }

    public function usersWithPredictions(int $season): EloquentCollection
    {
        return User::whereHas('predictions', function ($query) use ($season) {
            $query->where('season', $season)->where('status', 'scored');
        })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function availableSeasons(): array
    {
        return Prediction::distinct()
            ->pluck('season')
            ->sort()
            ->reverse()
            ->values()
            ->toArray();
    }

    public function topForSeason(int $season, int $limit = 5): EloquentCollection
    {
        return User::withSum(['predictions as total_score' => function ($query) use ($season) {
            $query->where('season', $season)->where('status', 'scored');
        }], 'score')
            ->whereHas('predictions', fn ($query) => $query->where('season', $season))
            ->orderByDesc('total_score')
            ->take($limit)
            ->get(['id', 'name'])
            ->each(function (User $user, int $index): void {
                $user->rank = $index + 1;
                $user->total_score = (int) ($user->total_score ?? 0);
            });
    }

    public function userRankForSeason(User $user, int $season): ?int
    {
        $ranked = User::withSum(['predictions as total_score' => function ($query) use ($season) {
            $query->where('season', $season)->where('status', 'scored');
        }], 'score')
            ->whereHas('predictions', fn ($query) => $query->where('season', $season))
            ->orderByDesc('total_score')
            ->get(['id']);

        $position = $ranked->search(fn (User $candidate) => (int) $candidate->id === (int) $user->id);

        return $position !== false ? $position + 1 : null;
    }

    private function isPerfectPrediction(Prediction $prediction): bool
    {
        if ($prediction->status !== 'scored') {
            return false;
        }

        return match ($prediction->type) {
            'race', 'sprint' => $prediction->race !== null
                && $this->raceScoring->buildBreakdown($prediction, $prediction->race)['perfect_bonus'] > 0,
            'midseason' => $this->championshipScoring->isPerfectPrediction($prediction, $prediction->season),
            default => false,
        };
    }
}
