<div class="max-w-4xl mx-auto p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            {{ $editingPrediction ? 'Edit Prediction' : ($race && $race->race_name ? "Predict: {$race->race_name}" : 'Create New Prediction') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Make your prediction for the {{ $type }} type prediction.
        </p>
    </div>

    @error('base')
        <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200 dark:bg-red-900/20 dark:border-red-700">
            <p class="text-sm text-red-800 dark:text-red-200">{{ $message }}</p>
        </div>
    @enderror

    <form wire:submit="save" class="space-y-8">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Prediction Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Prediction Type
                    </label>
                    <select 
                        wire:model.live="type"
                        id="type" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('type') border-red-500 @enderror"
                    >
                        <option value="race">Race Prediction</option>
                        <option value="sprint">Sprint Prediction</option>
                        <option value="preseason">Preseason Prediction</option>
                        <option value="midseason">Midseason Prediction</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Season -->
                <div>
                    <label for="season" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Season
                    </label>
                    <input 
                        type="number" 
                        wire:model.live="season"
                        id="season" 
                        min="2020" 
                        max="2030"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('season') border-red-500 @enderror"
                    >
                    @error('season')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Race Round (for race and sprint predictions) -->
                @if(in_array($type, ['race', 'sprint']))
                <div>
                    <label for="raceRound" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Race Round
                    </label>
                    <input 
                        type="number" 
                        wire:model="raceRound"
                        id="raceRound" 
                        min="1" 
                        max="25"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('raceRound') border-red-500 @enderror"
                    >
                    @error('raceRound')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                @endif
            </div>
        </div>

        <!-- Race Prediction Interface -->
        @if($type === 'race')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Race Prediction</h2>
            
            @if(!empty($drivers))
                <!-- Drag and Drop Driver List -->
                <livewire:predictions.draggable-driver-list 
                    :drivers="$drivers" 
                    :driver-order="$driverOrder"
                    :fastest-lap-driver-id="$fastestLapDriverId"
                    :race-name="$race?->race_name ?? 'Race'"
                    :season="$season"
                    :race-round="$raceRound"
                    wire:key="driver-list-{{ $season }}-{{ $raceRound }}"
                />
            @else
                <div class="text-center py-8">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <p>No drivers found for the selected season.</p>
                    </div>
                </div>
            @endif
        </div>
        @endif

        <!-- Preseason/Midseason Prediction Interface -->
        @if(in_array($type, ['preseason', 'midseason']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ ucfirst($type) }} Prediction</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Team Championship Order -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Team Championship Order</h3>
                    @if(!empty($teams))
                        <livewire:predictions.draggable-team-list 
                            :teams="$teams" 
                            :team-order="$teamOrder"
                            wire:key="team-list-{{ $season }}"
                        />
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No teams found for the selected season.</p>
                    @endif
                </div>

                <!-- Driver Championship Order -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Driver Championship Order</h3>
                    @if(!empty($drivers))
                        <livewire:predictions.draggable-driver-list 
                            :drivers="$drivers" 
                            :driver-order="$driverChampionship"
                            :race-name="'Driver Championship'"
                            :season="$season"
                            :race-round="0"
                            wire:key="driver-championship-{{ $season }}"
                        />
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No drivers found for the selected season.</p>
                    @endif
                </div>
            </div>

            <!-- Superlatives -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Superlatives</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Team with Most Podiums
                        </label>
                        <select 
                            wire:model="superlatives.most_podiums_team"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Select team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team['id'] }}">{{ $team['team_name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Driver with Most Podiums
                        </label>
                        <select 
                            wire:model="superlatives.most_podiums_driver"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Select driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver['id'] }}">{{ $driver['name'] }} {{ $driver['surname'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Team with Most DNFs
                        </label>
                        <select 
                            wire:model="superlatives.most_dnfs_team"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Select team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team['id'] }}">{{ $team['team_name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Driver with Most DNFs
                        </label>
                        <select 
                            wire:model="superlatives.most_dnfs_driver"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Select driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver['id'] }}">{{ $driver['name'] }} {{ $driver['surname'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Notes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Additional Notes</h2>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Notes (Optional)
                </label>
                <textarea 
                    wire:model="notes"
                    id="notes" 
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('notes') border-red-500 @enderror"
                    placeholder="Add any additional notes about your prediction..."
                ></textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-6">
            <div class="flex items-center space-x-4">
                <button 
                    type="button"
                    onclick="history.back()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Cancel
                </button>
            </div>
            <button 
                type="submit"
                @if(!$canEdit) disabled @endif
                class="px-6 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                    @if($canEdit)
                        bg-blue-600 text-white hover:bg-blue-700
                    @else
                        bg-gray-400 text-gray-100 cursor-not-allowed dark:bg-gray-600 dark:text-gray-300
                    @endif"
            >
                @if(!$canEdit && $editingPrediction)
                    Prediction Locked
                @else
                    {{ $editingPrediction ? 'Update Prediction' : 'Save Prediction' }}
                @endif
            </button>
        </div>
    </form>
</div>
