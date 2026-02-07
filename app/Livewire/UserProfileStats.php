<?php

namespace App\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Models\User;

class UserProfileStats extends Component
{
    #[Locked]
    public User $user;

    public int $season;
    public array $stats = [];
    public array $pointsChartData = [];
    public array $accuracyChartData = [];
    public array $heatmapData = [];
    public array $accuracyTrends = [];

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
        $this->accuracyChartData = $this->prepareAccuracyChartData();
        $this->heatmapData = $this->user->getPositionHeatmapData($this->season);
        $this->accuracyTrends = $this->user->getAccuracyTrends($this->season);
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

    private function prepareAccuracyChartData(): array
    {
        $data = $this->stats['accuracy_over_time'] ?? [];
        
        return [
            'labels' => array_column($data, 'race'),
            'datasets' => [
                [
                    'label' => 'Accuracy (%)',
                    'data' => array_map(fn($a) => round($a, 2), array_column($data, 'accuracy')),
                    'borderColor' => '#8B5CF6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.user-profile-stats');
    }
}
