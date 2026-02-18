<?php

namespace App\Livewire\Predictions;

use Livewire\Attributes\On;
use Livewire\Component;

class DraggableDriverList extends Component
{
    public array $drivers = [];

    public array $driverOrder = [];

    public ?string $fastestLapDriverId = null;

    public ?string $raceName = '';

    public int $season;

    public ?int $raceRound = 1;

    /** @var int Max positions for race/sprint order (e.g. 22). Only used when raceRound > 0. */
    public int $maxSlots = 22;

    /** @var string 'race'|'sprint' for race-order layout; DNF toggles only when type === 'race'. */
    public string $type = 'race';

    /** @var list<string> Driver IDs predicted to DNF (race only). Passed from parent for display; toggle dispatches to parent. */
    public array $dnfPredictions = [];

    public function mount(array $drivers = [], ?string $raceName = null, ?int $season = null, ?int $raceRound = 1, array $driverOrder = [], ?string $fastestLapDriverId = null, string $type = 'race', array $dnfPredictions = [])
    {
        $this->drivers = $drivers;
        $this->raceName = $raceName ?? '';
        $this->season = $season ?? (int) config('f1.current_season');
        $this->raceRound = $raceRound;
        $this->driverOrder = $driverOrder;
        $this->fastestLapDriverId = $fastestLapDriverId;
        $this->maxSlots = (int) config('f1.max_drivers', 22);
        $this->type = $type;
        $this->dnfPredictions = $dnfPredictions;

        // Only auto-fill for championship (single-list) mode; race/sprint start with empty slots
        if (empty($this->driverOrder) && $this->raceRound <= 0) {
            $this->driverOrder = collect($this->drivers)->pluck('id')->toArray();
        }
    }

    /** Slot index (0-based) from which drivers are outside points and can be toggled as DNF. */
    public function getDnfEligibleFromSlotProperty(): int
    {
        $bySeason = config('f1.points_positions_by_season', []);

        return (int) ($bySeason[$this->season] ?? 10);
    }

    /** True when showing two-column layout (slots + driver pool) for race/sprint. */
    public function getIsRaceOrderLayoutProperty(): bool
    {
        return $this->raceRound > 0;
    }

    public function updateDriverOrder(array $newOrder): void
    {
        $this->driverOrder = $newOrder;
        $this->dispatch('driver-order-updated', order: $newOrder);
    }

    public function setFastestLap(?string $driverId): void
    {
        $this->fastestLapDriverId = $driverId;
        $this->dispatch('fastest-lap-updated', driverId: $this->fastestLapDriverId);
    }

    /** Dispatch to parent to toggle DNF for a driver (race only). */
    public function toggleDnf(string $driverId): void
    {
        $this->dispatch('toggle-dnf', driverId: $driverId);
    }

    #[On('dnf-updated')]
    public function handleDnfUpdated(array $dnfPredictions): void
    {
        $this->dnfPredictions = $dnfPredictions;
    }

    public function render()
    {
        return view('livewire.predictions.draggable-driver-list');
    }
}
