<?php

namespace App\Livewire\Charts;

use App\Services\ChartDataService;
use Livewire\Component;

class PredictionAccuracyChart extends Component
{
    public int $season = 2024;

    public string $chartType = 'user-trends';

    public string $chartId;

    public array $chartData = [];

    public array $chartConfig = [];

    public function mount(int $season = 2024, string $chartType = 'user-trends')
    {
        $this->season = $season;
        $this->chartType = $chartType;
        $this->chartId = 'prediction-accuracy-chart-'.uniqid();
        $this->loadChartData();
    }

    public function updatedChartType(): void
    {
        $this->loadChartData();
    }

    public function updatedSeason()
    {
        $this->loadChartData();
    }

    private function loadChartData()
    {
        $chartService = app(ChartDataService::class);

        $user = auth()->user();

        $this->chartData = match ($this->chartType) {
            'user-trends' => $user
                ? $chartService->getUserPredictionAccuracyTrends($user, $this->season)
                : [],
            'user-comparison' => $chartService->getPredictionAccuracyComparison($this->season),
            'race-accuracy' => $chartService->getRacePredictionAccuracyByRace($this->season),
            'predictor-luck-variance' => $chartService->getPredictorLuckAndVariance($this->season),
            default => [],
        };

        $this->chartConfig = $chartService->getChartConfig('line', $this->chartData);

        $this->dispatch('chart-data-updated');
    }

    public function render()
    {
        return view('livewire.charts.prediction-accuracy-chart');
    }
}
