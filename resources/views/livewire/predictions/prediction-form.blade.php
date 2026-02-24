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
                        @php
                            $teammateTeamCount = collect($teamsWithDrivers)->filter(fn ($t) => count($t['drivers'] ?? []) >= 2)->count();
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($teamsWithDrivers as $team)
                                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden flex flex-col pt-1 relative">
                                    <!-- Constructor color strip -->
                                    @if(config('constructor_colors.' . strtolower($team['team_name'])))
                                        <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-200 dark:bg-zinc-700" style="background-color: {{ config('constructor_colors.' . strtolower($team['team_name'])) }}"></div>
                                    @else
                                        <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-200 dark:bg-zinc-800"></div>
                                    @endif
                                    
                                    <div class="px-3 py-2.5 border-b border-zinc-100 dark:border-zinc-800/60 bg-zinc-50/50 dark:bg-zinc-900/50 flex flex-col justify-center items-center text-center">
                                        <span class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{ $team['display_name'] }}</span>
                                    </div>
                                    
                                    <div class="p-3 grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 gap-3 flex-1">
                                        @if(count($team['drivers']) >= 2)
                                            @foreach($team['drivers'] as $driver)
                                                <label class="relative flex flex-col items-center justify-center p-3 cursor-pointer rounded-lg border-2 text-center transition-all duration-200
                                                    {{ ($teammateBattles[$team['id']] ?? null) == $driver['id'] 
                                                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' 
                                                        : 'border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-300 dark:hover:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}
                                                    {{ $isLocked ? 'pointer-events-none opacity-75' : '' }}">
                                                    
                                                    <input type="radio" name="teammateBattles[{{ $team['id'] }}]" value="{{ $driver['id'] }}"
                                                        wire:model.live="teammateBattles.{{ $team['id'] }}"
                                                        {{ $isLocked ? 'disabled' : '' }}
                                                        class="sr-only">
                                                        
                                                    <span class="text-xs opacity-75 mb-0.5 truncate w-full">{{ $driver['name'] }}</span>
                                                    <span class="text-sm font-bold uppercase tracking-wide truncate w-full">{{ $driver['surname'] }}</span>
                                                    
                                                    @if(($teammateBattles[$team['id']] ?? null) == $driver['id'])
                                                        <div class="absolute -top-2.5 -left-2.5 bg-white dark:bg-zinc-900 rounded-full text-primary-500 shadow-sm border border-zinc-100 dark:border-zinc-800">
                                                            <x-mary-icon name="o-check-circle" class="w-6 h-6 text-primary-600 dark:text-primary-500" />
                                                        </div>
                                                    @endif
                                                </label>
                                            @endforeach
                                        @else
                                            <div class="col-span-2 flex items-center justify-center p-4 text-center text-xs text-zinc-500 dark:text-zinc-400 italic bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-dashed border-zinc-200 dark:border-zinc-700">
                                                Lineup Unconfirmed
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($teammateTeamCount === 0)
                                <div class="col-span-full">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 italic">No constructor line-ups with two drivers are available for this season yet. Run <code class="text-xs bg-zinc-200 dark:bg-zinc-700 px-1 rounded">php artisan db:seed --class=DriverLineup2026Seeder</code> to assign drivers to constructors, then refresh this page.</p>
                                </div>
                            @endif
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
            <x-mary-button label="Cancel" link="{{ route('predictions.index') }}" variant="ghost" wire:loading.attr="disabled" wire:target="save" />
            @if(!$isLocked)
                <x-mary-button type="submit" label="{{ $editingPrediction ? 'Update Prediction' : 'Submit Prediction' }}" variant="primary" icon="o-check" spinner="save" />
            @endif
        </div>
    </form>
</div>
