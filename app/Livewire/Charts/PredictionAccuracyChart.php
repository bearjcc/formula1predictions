<?php

namespace App\Livewire\Charts;

use App\Services\ChartDataService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PredictionAccuracyChart extends Component
{
    public string $chartType = 'user-trends';
    public int $season = 2024;
    public string $chartId;
    public array $chartData = [];
    public array $chartConfig = [];

    public function mount(string $chartType = 'user-trends', int $season = 2024)
    {
        $this->chartType = $chartType;
        $this->season = $season;
        $this->chartId = 'prediction-accuracy-chart-' . uniqid();
        $this->loadChartData();
    }

    public function updatedChartType()
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

        switch ($this->chartType) {
            case 'user-trends':
                if (Auth::check()) {
                    $this->chartData = $chartService->getUserPredictionAccuracyTrends(Auth::user(), $this->season);
                } else {
                    $this->chartData = [];
                }
                break;
            case 'user-comparison':
                $this->chartData = $chartService->getPredictionAccuracyComparison($this->season);
                break;
            case 'race-accuracy':
                $this->chartData = $chartService->getRacePredictionAccuracyByRace($this->season);
                break;
            default:
                $this->chartData = [];
        }

        $this->chartConfig = $chartService->getChartConfig('bar', $this->chartData);
    }

    public function render()
    {
        return view('livewire.charts.prediction-accuracy-chart');
    }
}
