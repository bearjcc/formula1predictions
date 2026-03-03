<?php

declare(strict_types=1);

namespace App\Livewire\Predictions;

use Livewire\Component;

class DraggableTeamList extends Component
{
    public array $teams = [];

    public array $teamOrder = [];

    public string $title = 'Constructor Championship Order';

    public function mount(array $teams = [], array $teamOrder = [], string $title = 'Constructor Championship Order')
    {
        $this->teams = $teams;
        $this->title = $title;

        // Initialize team order from parent when provided; otherwise default to current teams order
        $this->teamOrder = ! empty($teamOrder)
            ? $teamOrder
            : collect($this->teams)->pluck('id')->toArray();
    }

    public function updateTeamOrder(array $newOrder): void
    {
        $this->teamOrder = $newOrder;
        $this->dispatch('team-order-updated', order: $newOrder)->to(PredictionForm::class);
    }

    public function getTeamOrderData(): array
    {
        return [
            'team_order' => $this->teamOrder,
        ];
    }

    public function render()
    {
        return view('livewire.predictions.draggable-team-list');
    }
}
