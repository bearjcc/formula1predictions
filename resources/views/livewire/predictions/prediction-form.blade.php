<div class="space-y-6">
    @if($isLocked)
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-6">
            <div class="flex items-center space-x-3">
                <x-mary-icon name="o-lock-closed" class="w-5 h-5 text-amber-600" />
                <p class="text-amber-800 dark:text-amber-200 font-medium">
                    This prediction is locked. The deadline has passed or the race results are being processed.
                </p>
            </div>
        </div>
    @endif

    @error('base')
        <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200 dark:bg-red-900/20 dark:border-red-700">
            <p class="text-sm text-red-800 dark:text-red-200">{{ $message }}</p>
        </div>
    @enderror

    <form wire:submit="save" class="space-y-8">
        @if($race)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white uppercase tracking-tight">
                {{ $race->display_name }} {{ $season }}
            </h2>
            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                <span>Round {{ $race->round }}</span>
                @if($race->date)
                    <span>{{ $race->date->format('j M Y') }}</span>
                @endif
                @if($race->circuit_name || $race->locality)
                    <span>{{ trim(implode(', ', array_filter([$race->circuit_name, $race->locality]))) }}</span>
                @endif
            </div>
            @if($this->predictionDeadline && !$isLocked)
                <p class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                    Closes 1 hour before {{ $type === 'sprint' ? 'sprint qualifying' : 'qualifying' }}: {{ $this->predictionDeadline->format('M j, Y g:i A T') }}
                </p>
            @endif
        </div>
        @elseif($type === 'preseason')
        <div class="mb-6">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white uppercase tracking-tight">
                Preseason {{ $season }}
            </h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                Full-season predictions. Due at the same time as the first race prediction.
            </p>
            @if($this->predictionDeadline && !$isLocked)
                <p class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                    Closes: {{ $this->predictionDeadline->format('M j, Y g:i A T') }}
                </p>
            @endif
        </div>
        @endif

        @if(in_array($type, ['race', 'sprint']))
            <div class="{{ $isLocked ? 'pointer-events-none grayscale-[0.5] opacity-80' : '' }}">
                @livewire('predictions.draggable-driver-list', [
                    'drivers' => $drivers,
                    'raceName' => $race ? $race->display_name : 'Race',
                    'season' => $season,
                    'raceRound' => $raceRound ?? 1,
                    'driverOrder' => $driverOrder,
                    'fastestLapDriverId' => $fastestLapDriverId,
                    'type' => $type,
                    'dnfPredictions' => $dnfPredictions,
                ], key('driver-list-' . ($race?->id ?? 'create')))
            </div>

        @else
            <div class="space-y-6 {{ $isLocked ? 'pointer-events-none opacity-80' : '' }}">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Constructor championship order</h3>
                @if(!empty($teams))
                    <livewire:predictions.draggable-team-list
                        :teams="$teams"
                        :team-order="$teamOrder"
                        wire:key="team-list-{{ $season }}"
                    />
                @else
                    <p class="text-zinc-500 dark:text-zinc-400">No constructors found for the selected season.</p>
                @endif

                @if($type === 'preseason')
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Teammate battles</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">For each constructor, pick who you think will finish higher in the championship.</p>
                        <div class="space-y-4">
                            @foreach($teamsWithDrivers as $team)
                                @if(count($team['drivers']) >= 2)
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-medium text-zinc-700 dark:text-zinc-300 w-40">{{ $team['display_name'] }}</span>
                                        <x-mary-select wire:model="teammateBattles.{{ $team['id'] }}" :disabled="$isLocked" placeholder="Pick driver" class="min-w-[180px]">
                                            @foreach($team['drivers'] as $driver)
                                                <option value="{{ $driver['id'] }}">{{ $driver['name'] }} {{ $driver['surname'] }}</option>
                                            @endforeach
                                        </x-mary-select>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('teammateBattles')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-mary-input label="Red flags (season total)" type="number" wire:model="redFlags" :disabled="$isLocked" min="0" placeholder="Optional" />
                            <x-mary-input label="Safety cars (season total)" type="number" wire:model="safetyCars" :disabled="$isLocked" min="0" placeholder="Optional" />
                        </div>
                    </div>
                @else
                    @if(!empty($drivers))
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Driver championship order</h3>
                            <livewire:predictions.draggable-driver-list
                                :drivers="$drivers"
                                :driver-order="$driverChampionship"
                                :race-name="'Driver Championship'"
                                :season="$season"
                                :race-round="0"
                                wire:key="driver-championship-{{ $season }}"
                            />
                        </div>
                    @else
                        <p class="text-zinc-500 dark:text-zinc-400">No drivers found for the selected season.</p>
                    @endif

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-4">Superlatives</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-mary-select label="Constructor with Most Podiums" wire:model="superlatives.most_podiums_team" :disabled="$isLocked">
                                <option value="">Select constructor</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team['id'] }}">{{ $team['display_name'] }}</option>
                                @endforeach
                            </x-mary-select>
                            <x-mary-select label="Driver with Most Podiums" wire:model="superlatives.most_podiums_driver" :disabled="$isLocked">
                                <option value="">Select driver</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver['id'] }}">{{ $driver['name'] }} {{ $driver['surname'] }}</option>
                                @endforeach
                            </x-mary-select>
                            <x-mary-select label="Constructor with Most DNFs" wire:model="superlatives.most_dnfs_team" :disabled="$isLocked">
                                <option value="">Select constructor</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team['id'] }}">{{ $team['display_name'] }}</option>
                                @endforeach
                            </x-mary-select>
                            <x-mary-select label="Driver with Most DNFs" wire:model="superlatives.most_dnfs_driver" :disabled="$isLocked">
                                <option value="">Select driver</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver['id'] }}">{{ $driver['name'] }} {{ $driver['surname'] }}</option>
                                @endforeach
                            </x-mary-select>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="flex items-center justify-end space-x-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <x-mary-button label="Cancel" link="{{ route('predictions.index') }}" variant="ghost" />
            @if(!$isLocked)
                <x-mary-button type="submit" label="{{ $editingPrediction ? 'Update Prediction' : 'Submit Prediction' }}" variant="primary" icon="o-check" />
            @endif
        </div>
    </form>
</div>
