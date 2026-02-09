<?php

namespace App\Livewire\Predictions;

use Livewire\Component;

class DraggableDriverList extends Component
{
    public array $drivers = [];

    public array $driverOrder = [];

    public ?string $fastestLapDriverId = null;

    public ?string $raceName = '';

    public int $season;

    public ?int $raceRound = 1;

    public function mount(array $drivers = [], ?string $raceName = null, ?int $season = null, ?int $raceRound = 1, array $driverOrder = [], ?string $fastestLapDriverId = null)
    {
        $this->drivers = $drivers;
        $this->raceName = $raceName ?? '';
        $this->season = $season ?? config('f1.current_season');
        $this->raceRound = $raceRound;
        $this->driverOrder = $driverOrder;
        $this->fastestLapDriverId = $fastestLapDriverId;

        if (empty($this->driverOrder)) {
            $this->driverOrder = collect($this->drivers)->pluck('id')->toArray();
        }
    }

    public function updateDriverOrder(array $newOrder): void
    {
        $this->driverOrder = $newOrder;
        $this->dispatch('driver-order-updated', order: $newOrder);
    }

    public function setFastestLap(string $driverId): void
    {
        $this->fastestLapDriverId = $this->fastestLapDriverId === $driverId ? null : $driverId;
        $this->dispatch('fastest-lap-updated', driverId: $this->fastestLapDriverId);
    }

    public function render()
    {
        return view('livewire.predictions.draggable-driver-list');
    }
}
