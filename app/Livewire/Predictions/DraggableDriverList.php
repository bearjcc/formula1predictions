<?php

namespace App\Livewire\Predictions;

use App\Models\Drivers;
use Livewire\Component;

class DraggableDriverList extends Component
{
    public array $drivers = [];
    public array $driverOrder = [];
    public ?int $selectedDriverId = null;
    public ?int $fastestLapDriverId = null;
    public string $raceName = '';
    public int $season = 2024;
    public int $raceRound = 1;

    public function mount(array $drivers = [], string $raceName = '', int $season = 2024, int $raceRound = 1)
    {
        $this->drivers = $drivers;
        $this->raceName = $raceName;
        $this->season = $season;
        $this->raceRound = $raceRound;
        
        // Initialize driver order if not provided
        if (empty($this->driverOrder)) {
            $this->driverOrder = collect($this->drivers)->pluck('id')->toArray();
        }
    }

    public function updateDriverOrder(array $newOrder)
    {
        $this->driverOrder = $newOrder;
    }

    public function setFastestLap(int $driverId)
    {
        $this->fastestLapDriverId = $driverId;
    }

    public function getDriverOrderData()
    {
        return [
            'driver_order' => $this->driverOrder,
            'fastest_lap' => $this->fastestLapDriverId,
        ];
    }

    public function render()
    {
        return view('livewire.predictions.draggable-driver-list');
    }
}

