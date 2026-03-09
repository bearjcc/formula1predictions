<?php

namespace App\Livewire;

use App\Services\LeaderboardService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;

class GlobalLeaderboard extends Component
{
    #[Url]
    public int $season;

    #[Url]
    public string $type = 'all'; // all, race, preseason

    #[Url]
    public string $sortBy = 'total_score'; // total_score, avg_score, predictions_count

    #[Url]
    public int $page = 1;

    public array $leaderboard = [];

    public int $perPage = 15;

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
        $this->resetPage();
        $this->loadLeaderboard();
    }

    public function updatedType(): void
    {
        $this->resetPage();
        $this->loadLeaderboard();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
        $this->sortLeaderboard();
    }

    public function resetPage(): void
    {
        $this->page = 1;
    }

    private function loadAvailableSeasons(): void
    {
        $fromDb = app(LeaderboardService::class)->availableSeasons();
        $extra = array_filter([
            $this->season,
            config('f1.current_season'),
        ]);
        $this->availableSeasons = array_values(array_unique(array_merge($fromDb, $extra)));
        rsort($this->availableSeasons);
    }

    private function loadLeaderboard(): void
    {
        $service = app(LeaderboardService::class);
        $collection = $service->seasonLeaderboard($this->season, $this->type);

        $this->leaderboard = $collection->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'initials' => strtoupper(substr($user->name, 0, 2)),
                'total_score' => $user->total_score ?? 0,
                'avg_score' => round($user->avg_score ?? 0, 2),
                'predictions_count' => $user->predictions_count,
                'perfect_predictions' => $user->perfect_predictions_count ?? 0,
                'rank' => $user->rank,
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
        $this->proStats = [
            'total_users' => $totalUsers,
            'total_predictions' => array_sum(array_column($this->leaderboard, 'predictions_count')),
            'avg_total_score' => round(array_sum($totalScores) / $totalUsers, 0),
            'median_score' => $this->calculateMedian($totalScores),
            'perfect_predictions' => array_sum(array_column($this->leaderboard, 'perfect_predictions')),
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
        $total = count($this->leaderboard);
        $currentPage = max(1, $this->page);
        $slice = array_slice($this->leaderboard, ($currentPage - 1) * $this->perPage, $this->perPage);
        $paginated = new LengthAwarePaginator(
            $slice,
            $total,
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query(), 'pageName' => 'page']
        );

        return view('livewire.global-leaderboard', [
            'paginatedLeaderboard' => $paginated,
        ]);
    }
}
