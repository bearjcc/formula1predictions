<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Locked;
use Livewire\Component;

class UserProfileStats extends Component
{
    #[Locked]
    public User $user;

    public int $season;

    public array $stats = [];

    public array $pointsChartData = [];

    public array $heatmapData = [];

    public function mount(User $user, ?int $season = null): void
    {
        $this->user = $user;
        $this->season = $season ?? (int) date('Y');
        $this->loadStats();
    }

    public function updatedSeason(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $this->stats = $this->user->getDetailedStats($this->season);
        $this->pointsChartData = $this->preparePointsChartData();
        $this->heatmapData = $this->user->getPositionHeatmapData($this->season);
    }

    private function preparePointsChartData(): array
    {
        $data = $this->stats['points_over_time'] ?? [];

        return [
            'labels' => array_column($data, 'race'),
            'datasets' => [
                [
                    'label' => 'Cumulative Points',
                    'data' => array_column($data, 'total'),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Race Points',
                    'data' => array_column($data, 'score'),
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.4,
                    'borderDash' => [5, 5],
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.user-profile-stats');
    }
}
