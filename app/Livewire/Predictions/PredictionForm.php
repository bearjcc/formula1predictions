<?php

declare(strict_types=1);

namespace App\Livewire\Predictions;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Teams;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

class PredictionForm extends Component
{
    #[Rule('required|string|in:race,sprint,preseason,midseason')]
    public string $type = 'race';

    #[Rule('required|integer|min:2020|max:2030')]
    public int $season;

    #[Rule('required_if:type,race,sprint|prohibited_if:type,preseason,midseason|integer|min:1|max:25')]
    public ?int $raceRound = null;

    #[Rule('nullable|string|max:1000')]
    public ?string $notes = null;

    // Race prediction data
    public array $driverOrder = [];

    public ?string $fastestLapDriverId = null;

    /** @var list<string> DNF wager: driver IDs predicted to DNF (race only). */
    public array $dnfPredictions = [];

    // Preseason/Midseason prediction data
    public array $teamOrder = [];

    public array $driverChampionship = [];

    public array $superlatives = [];

    // Available data
    public array $drivers = [];

    public array $teams = [];

    public ?Races $race = null;

    public ?Prediction $editingPrediction = null;

    public bool $isLocked = false;

    public function mount(?Races $race = null, ?Prediction $existingPrediction = null): void
    {
        $this->season = config('f1.current_season');
        $this->editingPrediction = null;
        $this->race = $race;
        $this->isLocked = false;

        $this->loadData();

        if ($existingPrediction !== null && $existingPrediction->exists) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            if ($user === null || $existingPrediction->user_id !== $user->id || ! $existingPrediction->isEditable()) {
                $this->isLocked = true;
            }

            $this->editingPrediction = $existingPrediction;
            $this->type = $existingPrediction->type ?? 'race';
            $this->season = $existingPrediction->season ?? config('f1.current_season');
            $this->raceRound = $existingPrediction->race_round;
            $this->notes = $existingPrediction->notes;

            if (in_array($this->type, ['race', 'sprint'], true) && $this->season && $this->raceRound !== null) {
                $this->race = Races::where('season', $this->season)->where('round', $this->raceRound)->first();
            }

            $predictionData = $existingPrediction->prediction_data ?? [];
            if (in_array($this->type, ['race', 'sprint'], true)) {
                $this->driverOrder = $predictionData['driver_order'] ?? $this->driverOrder;
                $this->fastestLapDriverId = (string) ($predictionData['fastest_lap'] ?? '');
                $rawDnf = $predictionData['dnf_predictions'] ?? [];
                $this->dnfPredictions = is_array($rawDnf) ? array_values(array_map('strval', $rawDnf)) : [];
            } else {
                $this->teamOrder = $predictionData['team_order'] ?? $this->teamOrder;
                $this->driverChampionship = $predictionData['driver_championship'] ?? $this->driverChampionship;
                $this->superlatives = $predictionData['superlatives'] ?? [];
            }

            if ($user !== null && $existingPrediction->user_id === $user->id) {
                $this->isLocked = ! $existingPrediction->isEditable();
            }
        } elseif ($race !== null) {
            $this->season = $race->season ?? config('f1.current_season');
            $this->raceRound = $race->round;
            $this->type = 'race';
            $this->isLocked = ! $race->allowsPredictions();
        }
    }

    public function loadData(): void
    {
        // For driver order, we use driverId (string) to match F1 API
        $this->drivers = Drivers::where('is_active', true)
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->driverId, // Using driverId string
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

        if (empty($this->driverOrder) && in_array($this->type, ['race', 'sprint'], true) && ! empty($this->drivers)) {
            $this->driverOrder = collect($this->drivers)->pluck('id')->toArray();
        }

        if (empty($this->teamOrder) && in_array($this->type, ['preseason', 'midseason']) && ! empty($this->teams)) {
            $this->teamOrder = collect($this->teams)->pluck('id')->toArray();
        }

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
        $this->dnfPredictions = [];
        $this->teamOrder = [];
        $this->driverChampionship = [];
        $this->superlatives = [];
    }

    public function updateDriverOrder(array $newOrder): void
    {
        if ($this->isLocked) {
            return;
        }
        $this->driverOrder = $newOrder;
    }

    public function toggleDnfDriver(string $driverId): void
    {
        if ($this->isLocked || $this->type !== 'race') {
            return;
        }
        $key = array_search($driverId, $this->dnfPredictions, true);
        if ($key !== false) {
            unset($this->dnfPredictions[$key]);
            $this->dnfPredictions = array_values($this->dnfPredictions);
        } else {
            $this->dnfPredictions[] = $driverId;
        }
    }

    public function setFastestLap(string $driverId): void
    {
        if ($this->isLocked) {
            return;
        }
        $this->fastestLapDriverId = $this->fastestLapDriverId === $driverId ? null : $driverId;
    }

    public function updateTeamOrder(array $newOrder): void
    {
        if ($this->isLocked) {
            return;
        }
        $this->teamOrder = $newOrder;
    }

    #[On('driver-order-updated')]
    public function handleDriverOrderUpdated(array $order): void
    {
        if ($this->isLocked) {
            return;
        }
        $this->driverOrder = $order;
    }

    #[On('fastest-lap-updated')]
    public function handleFastestLapUpdated(?string $driverId): void
    {
        if ($this->isLocked) {
            return;
        }
        $this->fastestLapDriverId = $driverId;
    }

    #[On('team-order-updated')]
    public function handleTeamOrderUpdated(array $order): void
    {
        if ($this->isLocked) {
            return;
        }
        $this->teamOrder = $order;
    }

    public function updateDriverChampionship(array $newOrder): void
    {
        if ($this->isLocked) {
            return;
        }
        $this->driverChampionship = $newOrder;
    }

    public function updateSuperlative(string $key, $value): void
    {
        if ($this->isLocked) {
            return;
        }
        $this->superlatives[$key] = $value;
    }

    public function getCanEditProperty(): bool
    {
        return ! $this->isLocked;
    }

    /**
     * Get the prediction deadline for display (race or sprint, 1 hour before qualifying).
     */
    public function getPredictionDeadlineProperty(): ?\Carbon\Carbon
    {
        if ($this->race === null || ! in_array($this->type, ['race', 'sprint'], true)) {
            return null;
        }

        return $this->type === 'sprint'
            ? $this->race->getSprintPredictionDeadline()
            : $this->race->getRacePredictionDeadline();
    }

    public function save(): void
    {
        if ($this->isLocked) {
            $this->addError('base', 'This prediction can no longer be edited.');

            return;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($this->editingPrediction !== null && ($user === null || $this->editingPrediction->user_id !== $user->id || ! $this->editingPrediction->isEditable())) {
            $this->addError('base', 'This prediction can no longer be edited.');

            return;
        }

        $this->validate([
            'type' => 'required|string|in:race,sprint,preseason,midseason',
            'season' => 'required|integer|min:2020|max:2030',
            'raceRound' => 'required_if:type,race,sprint|prohibited_if:type,preseason,midseason|integer|min:1|max:25',
            'notes' => 'nullable|string|max:1000',
            'driverOrder' => 'required_if:type,race,sprint|array|min:1|max:'.config('f1.max_drivers', 22),
            'driverOrder.*' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $exists = is_numeric($value)
                        ? \App\Models\Drivers::where('id', (int) $value)->exists()
                        : \App\Models\Drivers::where('driver_id', (string) $value)->exists();
                    if (! $exists) {
                        $fail(__('The selected driver is invalid.'));
                    }
                },
            ],
            'teamOrder' => [
                'required_if:type,preseason,midseason',
                'array',
                ValidationRule::when(in_array($this->type, ['preseason', 'midseason'], true), 'min:1|max:'.config('f1.max_teams', 11), 'min:0|max:'.config('f1.max_teams', 11)),
            ],
            'teamOrder.*' => 'integer|exists:teams,id',
            'driverChampionship' => [
                'required_if:type,preseason,midseason',
                'array',
                ValidationRule::when(in_array($this->type, ['preseason', 'midseason'], true), 'min:1|max:'.config('f1.max_drivers', 22), 'min:0|max:'.config('f1.max_drivers', 22)),
            ],
            'driverChampionship.*' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $exists = is_numeric($value)
                        ? \App\Models\Drivers::where('id', (int) $value)->exists()
                        : \App\Models\Drivers::where('driver_id', (string) $value)->exists();
                    if (! $exists) {
                        $fail(__('The selected driver is invalid.'));
                    }
                },
            ],
        ]);

        if ($this->type === 'sprint' && ($this->race === null || ! $this->race->hasSprint())) {
            $this->addError('type', 'Sprint predictions are only available for races that have a sprint session.');

            return;
        }

        $predictionData = $this->buildPredictionData();

        if ($this->editingPrediction !== null) {
            $this->editingPrediction->update([
                'type' => $this->type,
                'season' => $this->season,
                'race_round' => $this->raceRound,
                'race_id' => $this->race?->id ?? $this->editingPrediction->race_id,
                'prediction_data' => $predictionData,
                'notes' => $this->notes,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            session()->flash('success', 'Prediction updated successfully.');
        } else {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $user->predictions()->create([
                'type' => $this->type,
                'season' => $this->season,
                'race_round' => $this->raceRound,
                'race_id' => $this->race?->id,
                'prediction_data' => $predictionData,
                'notes' => $this->notes,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            session()->flash('success', 'Prediction created successfully.');
        }

        $this->redirect(route('predictions.index'));
    }

    private function buildPredictionData(): array
    {
        $data = [];

        if (in_array($this->type, ['race', 'sprint'], true)) {
            $data['driver_order'] = $this->driverOrder;
            if ($this->fastestLapDriverId) {
                $data['fastest_lap'] = $this->fastestLapDriverId;
            }
            if ($this->type === 'race' && ! empty($this->dnfPredictions)) {
                $data['dnf_predictions'] = array_values($this->dnfPredictions);
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
