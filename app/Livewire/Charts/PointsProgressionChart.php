<?php

namespace App\Livewire\Charts;

use App\Services\ChartDataService;
use Livewire\Component;

class PointsProgressionChart extends Component
{
    public int $season = 2024;
    public string $chartType = 'driver'; // 'driver' or 'team'
    public string $chartId;
    public array $chartData = [];
    public array $chartConfig = [];

    public function mount(int $season = 2024, string $chartType = 'driver')
    {
        $this->season = $season;
        $this->chartType = $chartType;
        $this->chartId = 'points-progression-chart-' . uniqid();
        $this->loadChartData();
    }

    public function updatedSeason()
    {
        $this->loadChartData();
    }

    public function updatedChartType()
    {
        $this->loadChartData();
    }

    private function loadChartData()
    {
        $chartService = app(ChartDataService::class);

        if ($this->chartType === 'driver') {
            $this->chartData = $chartService->getDriverPointsProgression($this->season);
        } else {
            $this->chartData = $chartService->getTeamPointsProgression($this->season);
        }

        $this->chartConfig = $chartService->getChartConfig('line', $this->chartData);
    }

    public function render()
    {
        return view('livewire.charts.points-progression-chart');
    }
}