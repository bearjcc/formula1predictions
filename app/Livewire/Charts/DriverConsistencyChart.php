<?php

namespace App\Livewire\Charts;

use App\Services\ChartDataService;
use Livewire\Component;

class DriverConsistencyChart extends Component
{
    public int $season = 2024;

    public string $chartId;

    public array $chartData = [];

    public array $chartConfig = [];

    public function mount(int $season = 2024)
    {
        $this->season = $season;
        $this->chartId = 'driver-consistency-chart-'.uniqid();
        $this->loadChartData();
    }

    public function updatedSeason()
    {
        $this->loadChartData();
    }

    private function loadChartData()
    {
        $chartService = app(ChartDataService::class);
        $this->chartData = $chartService->getDriverConsistencyAnalysis($this->season);
        $this->chartConfig = $chartService->getChartConfig('bar', $this->chartData);
    }

    public function render()
    {
        return view('livewire.charts.driver-consistency-chart');
    }
}
