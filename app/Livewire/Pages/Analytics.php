<?php

namespace App\Livewire\Pages;

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Services\ChartDataService;
use Livewire\Component;

class Analytics extends Component
{
    public int $selectedSeason;

    public function mount(): void
    {
        $this->selectedSeason = config('f1.current_season');
    }

    public function render()
    {
        $chartService = app(ChartDataService::class);

        $data = [
            'selectedSeason' => $this->selectedSeason,
            'totalPredictions' => Prediction::where('season', $this->selectedSeason)->count(),
            'activeUsers' => User::whereHas('predictions', function ($q) {
                $q->where('season', $this->selectedSeason);
            })->count(),
            'racesCompleted' => Races::where('season', $this->selectedSeason)->whereNotNull('results')->count(),
            'avgAccuracy' => number_format(Prediction::where('season', $this->selectedSeason)->whereNotNull('accuracy')->avg('accuracy') ?? 0, 1),
            'driverData' => $chartService->getDriverPerformanceComparison($this->selectedSeason),
            'teamData' => $chartService->getTeamPerformanceComparison($this->selectedSeason),
            'raceAccuracyData' => $chartService->getRacePredictionAccuracyByRace($this->selectedSeason),
            'userComparisonData' => $chartService->getPredictionAccuracyComparison($this->selectedSeason),
            'predictionTypeData' => $chartService->getPredictionAccuracyByType($this->selectedSeason),
            'raceDistributionData' => $chartService->getRaceResultDistribution($this->selectedSeason),
        ];

        return view('livewire.pages.analytics', $data);
    }
}
