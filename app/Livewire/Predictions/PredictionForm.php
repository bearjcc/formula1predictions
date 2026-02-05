<?php

declare(strict_types=1);

namespace App\Livewire\Predictions;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Teams;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

class PredictionForm extends Component
{
    #[Rule('required|string|in:race,preseason,midseason')]
    public string $type = 'race';

    #[Rule('required|integer|min:2020|max:2030')]
    public int $season = 2024;

    #[Rule('required_if:type,race|prohibited_if:type,preseason,midseason|integer|min:1|max:25')]
    public ?int $raceRound = null;

    #[Rule('nullable|string|max:1000')]
    public ?string $notes = null;

    // Race prediction data
    public array $driverOrder = [];

    public ?int $fastestLapDriverId = null;

    // Preseason/Midseason prediction data
    public array $teamOrder = [];

    public array $driverChampionship = [];

    public array $superlatives = [];

    // Available data
    public array $drivers = [];

    public array $teams = [];

    public ?Races $race = null;

    public ?Prediction $editingPrediction = null;

    public bool $canEdit = true;

    public function mount(?Races $race = null, ?Prediction $existingPrediction = null): void
    {
        // Ensure editingPrediction is null by default
        $this->editingPrediction = null;
        $this->race = $race;
        $this->canEdit = true;

        if ($existingPrediction !== null && $existingPrediction->exists) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user === null || $existingPrediction->user_id !== $user->id || ! $existingPrediction->isEditable()) {
                $this->canEdit = false;
                $this->editingPrediction = $existingPrediction;

                $this->loadData();

                return;
            }

            // Editing existing prediction
            $this->editingPrediction = $existingPrediction;
            $this->type = $existingPrediction->type ?? 'race';
            $this->season = $existingPrediction->season ?? 2024;
            $this->raceRound = $existingPrediction->race_round;
            $this->notes = $existingPrediction->notes;

            // Load existing prediction data
            $predictionData = $existingPrediction->prediction_data ?? [];
            if ($this->type === 'race') {
                $this->driverOrder = $predictionData['driver_order'] ?? [];
                $this->fastestLapDriverId = $predictionData['fastest_lap'] ?? null;
            } else {
                $this->teamOrder = $predictionData['team_order'] ?? [];
                $this->driverChampionship = $predictionData['driver_championship'] ?? [];
                $this->superlatives = $predictionData['superlatives'] ?? [];
            }
        } elseif ($race !== null) {
            // Creating new prediction for specific race
            $this->season = $race->season ?? 2024;
            $this->raceRound = $race->round;
            $this->type = 'race';
        }

        $this->loadData();
    }

    public function loadData(): void
    {
        // Load drivers (not filtered by season since they don't have season column)
        $this->drivers = Drivers::with('team')
            ->where('is_active', true)
            ->orderBy('surname')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'surname' => $driver->surname,
                    'nationality' => $driver->nationality,
                    'team' => [
                        'id' => $driver->team?->id,
                        'team_name' => $driver->team?->team_name,
                    ],
                ];
            })
            ->toArray();

        // Load teams (not filtered by season since they don't have season column)
        $this->teams = Teams::where('is_active', true)
            ->orderBy('team_name')
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'team_name' => $team->team_name,
                    'nationality' => $team->nationality,
                ];
            })
            ->toArray();

        // Initialize driver order if not set and drivers exist
        if (empty($this->driverOrder) && $this->type === 'race' && ! empty($this->drivers)) {
            $this->driverOrder = collect($this->drivers)->pluck('id')->toArray();
        }

        // Initialize team order if not set and teams exist
        if (empty($this->teamOrder) && in_array($this->type, ['preseason', 'midseason']) && ! empty($this->teams)) {
            $this->teamOrder = collect($this->teams)->pluck('id')->toArray();
        }

        // Initialize driver championship if not set and drivers exist
        if (empty($this->driverChampionship) && in_array($this->type, ['preseason', 'midseason']) && ! empty($this->drivers)) {
            $this->driverChampionship = collect($this->drivers)->pluck('id')->toArray();
        }
    }

    public function updatedType(): void
    {
        $this->resetPredictionData();
        $this->loadData();
    }

    public function updatedSeason(): void
    {
        $this->resetPredictionData();
        $this->loadData();
    }

    public function resetPredictionData(): void
    {
        $this->driverOrder = [];
        $this->fastestLapDriverId = null;
        $this->teamOrder = [];
        $this->driverChampionship = [];
        $this->superlatives = [];
    }

    public function updateDriverOrder(array $newOrder): void
    {
        $this->driverOrder = $newOrder;
    }

    public function setFastestLap(int $driverId): void
    {
        $this->fastestLapDriverId = $this->fastestLapDriverId === $driverId ? null : $driverId;
    }

    public function updateTeamOrder(array $newOrder): void
    {
        $this->teamOrder = $newOrder;
    }

    #[On('driver-order-updated')]
    public function handleDriverOrderUpdated(array $order): void
    {
        $this->driverOrder = $order;
    }

    #[On('fastest-lap-updated')]
    public function handleFastestLapUpdated(?int $driverId): void
    {
        $this->fastestLapDriverId = $driverId;
    }

    #[On('team-order-updated')]
    public function handleTeamOrderUpdated(array $order): void
    {
        $this->teamOrder = $order;
    }

    public function updateDriverChampionship(array $newOrder): void
    {
        $this->driverChampionship = $newOrder;
    }

    public function updateSuperlative(string $key, $value): void
    {
        $this->superlatives[$key] = $value;
    }

    public function save(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($this->editingPrediction !== null) {
            if ($user === null || $this->editingPrediction->user_id !== $user->id || ! $this->editingPrediction->isEditable()) {
                $this->addError('base', 'This prediction can no longer be edited.');

                return;
            }
        }

        $this->validate([
            'type' => 'required|string|in:race,preseason,midseason',
            'season' => 'required|integer|min:2020|max:2030',
            'raceRound' => 'required_if:type,race|prohibited_if:type,preseason,midseason|integer|min:1|max:25',
            'notes' => 'nullable|string|max:1000',
            'driverOrder' => 'required_if:type,race|array',
            'teamOrder' => 'required_if:type,preseason,midseason|array',
            'driverChampionship' => 'required_if:type,preseason,midseason|array',
        ]);

        $predictionData = $this->buildPredictionData();

        if ($this->editingPrediction !== null) {
            // Update existing prediction
            $this->editingPrediction->update([
                'type' => $this->type,
                'season' => $this->season,
                'race_round' => $this->raceRound,
                'race_id' => $this->race?->id,
                'prediction_data' => $predictionData,
                'notes' => $this->notes,
            ]);

            $prediction = $this->editingPrediction;
        } else {
            // Create new prediction
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $prediction = $user->predictions()->create([
                'type' => $this->type,
                'season' => $this->season,
                'race_round' => $this->raceRound,
                'race_id' => $this->race?->id,
                'prediction_data' => $predictionData,
                'notes' => $this->notes,
                'status' => 'draft',
            ]);
        }

        $this->redirect(route('predictions.index'));
    }

    private function buildPredictionData(): array
    {
        $data = [];

        if ($this->type === 'race') {
            $data['driver_order'] = $this->driverOrder;
            if ($this->fastestLapDriverId) {
                $data['fastest_lap'] = $this->fastestLapDriverId;
            }
        } else {
            $data['team_order'] = $this->teamOrder;
            $data['driver_championship'] = $this->driverChampionship;
            if (! empty($this->superlatives)) {
                $data['superlatives'] = $this->superlatives;
            }
        }

        return $data;
    }

    public function render()
    {
        return view('livewire.predictions.prediction-form');
    }
}
