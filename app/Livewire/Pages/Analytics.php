<?php

namespace App\Livewire\Pages;

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
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
        $data = [
            'selectedSeason' => $this->selectedSeason,
            'totalPredictions' => Prediction::where('season', $this->selectedSeason)->count(),
            'activeUsers' => User::whereHas('predictions', function ($q) {
                $q->where('season', $this->selectedSeason);
            })->count(),
            'racesCompleted' => Races::where('season', $this->selectedSeason)->whereNotNull('results')->count(),
            'avgScore' => number_format(Prediction::where('season', $this->selectedSeason)->whereNotNull('score')->avg('score') ?? 0, 1),
        ];

        return view('livewire.pages.analytics', $data);
    }
}
