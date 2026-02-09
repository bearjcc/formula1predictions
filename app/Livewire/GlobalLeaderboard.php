<?php

namespace App\Livewire;

use App\Models\Prediction;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;

class GlobalLeaderboard extends Component
{
    #[Url]
    public int $season;

    #[Url]
    public string $type = 'all'; // all, race, preseason

    #[Url]
    public string $sortBy = 'total_score'; // total_score, avg_score, accuracy, predictions

    public array $leaderboard = [];

    public array $proStats = [];

    public array $availableSeasons = [];

    public string $chartId = 'leaderboard-chart';

    public function mount(): void
    {
        $this->season = $this->season ?: config('f1.current_season');
        $this->loadAvailableSeasons();
        $this->loadLeaderboard();
    }

    public function updatedSeason(): void
    {
        $this->loadLeaderboard();
    }

    public function updatedType(): void
    {
        $this->loadLeaderboard();
    }

    public function updatedSortBy(): void
    {
        $this->sortLeaderboard();
    }

    private function loadAvailableSeasons(): void
    {
        $this->availableSeasons = Prediction::distinct()
            ->pluck('season')
            ->sort()
            ->reverse()
            ->values()
            ->toArray();
    }

    private function loadLeaderboard(): void
    {
        $query = User::withCount(['predictions' => function ($query) {
            $query->where('season', $this->season);
            if ($this->type !== 'all') {
                $query->where('type', $this->type);
            }
        }])
            ->withSum(['predictions as total_score' => function ($query) {
                $query->where('season', $this->season)
                    ->where('status', 'scored');
                if ($this->type !== 'all') {
                    $query->where('type', $this->type);
                }
            }], 'score')
            ->withAvg(['predictions as avg_score' => function ($query) {
                $query->where('season', $this->season)
                    ->where('status', 'scored');
                if ($this->type !== 'all') {
                    $query->where('type', $this->type);
                }
            }], 'score')
            ->withAvg(['predictions as avg_accuracy' => function ($query) {
                $query->where('season', $this->season)
                    ->where('status', 'scored');
                if ($this->type !== 'all') {
                    $query->where('type', $this->type);
                }
            }], 'accuracy')
            ->withCount(['predictions as perfect_predictions_count' => function ($query) {
                $query->where('season', $this->season)
                    ->where('status', 'scored')
                    ->where('score', '>=', 25);
                if ($this->type !== 'all') {
                    $query->where('type', $this->type);
                }
            }])
            ->whereHas('predictions', function ($query) {
                $query->where('season', $this->season);
                if ($this->type !== 'all') {
                    $query->where('type', $this->type);
                }
            })
            ->get();

        $this->leaderboard = $query->map(function ($user, $index) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'initials' => strtoupper(substr($user->name, 0, 2)),
                'total_score' => $user->total_score ?? 0,
                'avg_score' => round($user->avg_score ?? 0, 2),
                'avg_accuracy' => round($user->avg_accuracy ?? 0, 2),
                'predictions_count' => $user->predictions_count,
                'perfect_predictions' => $user->perfect_predictions_count ?? 0,
                'is_supporter' => $user->is_season_supporter,
                'badges' => $user->getBadges(),
            ];
        })->toArray();

        $this->sortLeaderboard();
        $this->loadProStats();
    }

    private function sortLeaderboard(): void
    {
        usort($this->leaderboard, function ($a, $b) {
            $fieldA = $a[$this->sortBy] ?? 0;
            $fieldB = $b[$this->sortBy] ?? 0;

            return $fieldB <=> $fieldA; // Descending
        });

        // Add ranks
        foreach ($this->leaderboard as $index => &$user) {
            $user['rank'] = $index + 1;
        }
    }

    private function loadProStats(): void
    {
        // Calculate leaderboard-wide statistics
        $totalUsers = count($this->leaderboard);
        if ($totalUsers === 0) {
            $this->proStats = [];

            return;
        }

        $totalScores = array_column($this->leaderboard, 'total_score');
        $avgScores = array_column($this->leaderboard, 'avg_score');
        $accuracies = array_column($this->leaderboard, 'avg_accuracy');

        $this->proStats = [
            'total_users' => $totalUsers,
            'total_predictions' => array_sum(array_column($this->leaderboard, 'predictions_count')),
            'avg_total_score' => round(array_sum($totalScores) / $totalUsers, 0),
            'median_score' => $this->calculateMedian($totalScores),
            'avg_accuracy' => round(array_sum($accuracies) / $totalUsers, 2),
            'perfect_predictions' => array_sum(array_column($this->leaderboard, 'perfect_predictions')),
            'supporters' => array_sum(array_column($this->leaderboard, fn ($u) => $u['is_supporter'] ? 1 : 0)),
        ];
    }

    private function calculateMedian(array $values): int|float
    {
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);

        if ($count % 2) {
            return $values[$middle];
        }

        return ($values[$middle - 1] + $values[$middle]) / 2;
    }

    public function render()
    {
        return view('livewire.global-leaderboard');
    }
}
