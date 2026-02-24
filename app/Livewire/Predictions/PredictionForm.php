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

    // Race prediction data
    public array $driverOrder = [];

    public ?string $fastestLapDriverId = null;

    /** @var list<string> DNF wager: driver IDs predicted to DNF (race only). */
    public array $dnfPredictions = [];

    // Preseason/Midseason prediction data
    public array $teamOrder = [];

    public array $driverChampionship = [];

    /** @var array<int, int> team_id => driver_id (preseason teammate battles) */
    public array $teammateBattles = [];

    public ?int $redFlags = null;

    public ?int $safetyCars = null;

    public array $superlatives = [];

    // Available data
    public array $drivers = [];

    public array $teams = [];

    /** Teams with drivers for preseason teammate battles. */
    public array $teamsWithDrivers = [];

    public ?Races $race = null;

    public ?Prediction $editingPrediction = null;

    public bool $isLocked = false;

    public function mount(
        ?Races $race = null,
        ?Prediction $existingPrediction = null,
        bool $preseason = false,
        ?int $preseasonYear = null
    ): void {
        $this->season = config('f1.current_season');
        $this->editingPrediction = null;
        $this->race = $race;
        $this->isLocked = false;

        if ($preseason) {
            $this->type = 'preseason';
            $this->season = $preseasonYear ?? config('f1.current_season');
            $deadline = Races::getPreseasonDeadlineForSeason($this->season);
            $this->isLocked = $deadline === null || ! $deadline->isFuture();
        }

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
                $rawTb = $predictionData['teammate_battles'] ?? [];
                if (is_array($rawTb)) {
                    $this->teammateBattles = [];
                    foreach ($rawTb as $k => $v) {
                        $this->teammateBattles[(int) $k] = (int) $v;
                    }
                }
                $this->redFlags = isset($predictionData['red_flags']) ? (int) $predictionData['red_flags'] : null;
                $this->safetyCars = isset($predictionData['safety_cars']) ? (int) $predictionData['safety_cars'] : null;
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
        // Use driver_id (F1 API string) when present, else fallback to id for compatibility
        $this->drivers = Drivers::where('is_active', true)
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->driver_id ?? (string) $driver->id,
                    'name' => $driver->name,
                    'surname' => $driver->surname,
                    'nationality' => $driver->nationality,
                    'team' => [
                        'id' => $driver->team?->id,
                        'team_name' => $driver->team?->team_name,
                        'display_name' => $driver->team?->display_name,
                    ],
                ];
            })
            ->values()
            ->toArray();

        $this->teams = Teams::where('is_active', true)
            ->with('drivers')
            ->orderBy('team_name')
            ->get()
            ->map(function ($team) {
                $driverSurnames = $team->drivers->pluck('surname')->filter()->values();

                return [
                    'id' => $team->id,
                    'team_name' => $team->team_name,
                    'display_name' => $team->display_name,
                    'nationality' => $team->nationality,
                    'driver_surnames' => $driverSurnames->count() >= 1
                        ? $driverSurnames->join(', ')
                        : null,
                ];
            })
            ->toArray();

        $this->teamsWithDrivers = Teams::where('is_active', true)
            ->with('drivers')
            ->orderBy('team_name')
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'team_name' => $team->team_name,
                    'display_name' => $team->display_name,
                    'drivers' => $team->drivers->map(fn ($d) => [
                        'id' => $d->id,
                        'name' => $d->name,
                        'surname' => $d->surname,
                    ])->values()->toArray(),
                ];
            })
            ->toArray();

        // Race/sprint: do not pre-fill driver order; user builds it by dragging from pool
        if (empty($this->teamOrder) && in_array($this->type, ['preseason', 'midseason']) && ! empty($this->teams)) {
            $this->teamOrder = collect($this->teams)->pluck('id')->toArray();
        }

        if (empty($this->driverChampionship) && $this->type === 'midseason' && ! empty($this->drivers)) {
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
        $this->teammateBattles = [];
        $this->redFlags = null;
        $this->safetyCars = null;
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

    #[On('toggle-dnf')]
    public function handleToggleDnf(string $driverId): void
    {
        $this->toggleDnfDriver($driverId);
        $this->dispatch('dnf-updated', dnfPredictions: $this->dnfPredictions)->to(DraggableDriverList::class);
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
     * Get the prediction deadline for display (race or sprint, 1 hour before qualifying; preseason = first race deadline).
     */
    public function getPredictionDeadlineProperty(): ?\Carbon\Carbon
    {
        if ($this->type === 'preseason') {
            return Races::getPreseasonDeadlineForSeason($this->season);
        }

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
            'driverOrder' => [
                'required_if:type,race,sprint',
                'array',
                'min:1',
                'max:'.config('f1.max_drivers', 22),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! in_array($this->type, ['race', 'sprint'], true)) {
                        return;
                    }
                    if (empty(array_filter($value ?? []))) {
                        $fail(__('At least one driver must be placed in the prediction.'));
                    }
                },
            ],
            'driverOrder.*' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $exists = is_numeric($value)
                        ? \App\Models\Drivers::where('id', (int) $value)->exists()
                        : \App\Models\Drivers::where('driver_id', (string) $value)->exists();
                    if (! $exists) {
                        $fail(__('The selected driver is invalid.'));
                    }
                },
            ],
            'dnfPredictions' => [
                ValidationRule::when($this->type === 'race', 'array|max:12', 'nullable'),
            ],
            'dnfPredictions.*' => [
                'required',
                'string',
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
                ValidationRule::when(in_array($this->type, ['preseason', 'midseason'], true), 'min:1|max:'.config('f1.max_constructors', 11), 'min:0|max:'.config('f1.max_constructors', 11)),
            ],
            'teamOrder.*' => 'integer|exists:teams,id',
            'driverChampionship' => [
                'required_if:type,midseason',
                'array',
                ValidationRule::when($this->type === 'midseason', 'min:1|max:'.config('f1.max_drivers', 22), 'nullable'),
            ],
            'driverChampionship.*' => [
                'required_if:type,midseason',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! in_array($this->type, ['midseason'], true)) {
                        return;
                    }
                    $exists = is_numeric($value)
                        ? \App\Models\Drivers::where('id', (int) $value)->exists()
                        : \App\Models\Drivers::where('driver_id', (string) $value)->exists();
                    if (! $exists) {
                        $fail(__('The selected driver is invalid.'));
                    }
                },
            ],
            'teammateBattles' => ['nullable', 'array'],
            'teammateBattles.*' => ['required', 'integer', 'exists:drivers,id'],
            'redFlags' => ['nullable', 'integer', 'min:0'],
            'safetyCars' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($this->type === 'preseason' && ! empty($this->teammateBattles)) {
            foreach ($this->teammateBattles as $teamId => $driverId) {
                $driver = Drivers::find($driverId);
                if ($driver && (int) $driver->team_id !== (int) $teamId) {
                    $this->addError('teammateBattles', __('The selected driver must belong to that team.'));

                    return;
                }
            }
        }

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
            ]);
            $this->editingPrediction->submit();

            session()->flash('success', 'Prediction updated successfully.');
        } else {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $prediction = $user->predictions()->create([
                'type' => $this->type,
                'season' => $this->season,
                'race_round' => $this->raceRound,
                'race_id' => $this->race?->id,
                'prediction_data' => $predictionData,
            ]);
            $prediction->submit();

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
            if ($this->type === 'midseason') {
                $data['driver_championship'] = $this->driverChampionship;
            }
            if ($this->type === 'preseason') {
                $data['teammate_battles'] = array_filter($this->teammateBattles, fn ($v) => $v !== null && $v !== '');
                if ($this->redFlags !== null) {
                    $data['red_flags'] = $this->redFlags;
                }
                if ($this->safetyCars !== null) {
                    $data['safety_cars'] = $this->safetyCars;
                }
            }
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
