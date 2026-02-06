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

    <form wire:submit.prevent="save" class="space-y-8">
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-mary-select label="Prediction Type" wire:model.live="type" :disabled="$isLocked || $editingPrediction !== null">
                    <option value="race">Race Results</option>
                    <option value="preseason">Pre-Season Championship Only</option>
                    <option value="midseason">Mid-Season Revision</option>
                </x-mary-select>

                <x-mary-select label="Season" wire:model.live="season" :disabled="$isLocked || $editingPrediction !== null">
                    @foreach(range(date('Y'), 2020) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </x-mary-select>
            </div>

            @if($type === 'race')
                <div class="mt-6">
                    <x-mary-input label="Race Round" wire:model="raceRound" type="number" readonly />
                    @if($race)
                        <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Predicting for: <strong>{{ $race->race_name }}</strong>
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-6">
                <x-mary-textarea label="Notes" wire:model="notes" placeholder="Your thoughts on this prediction..." :disabled="$isLocked" />
            </div>
        </div>

        @if($type === 'race')
            <div class="{{ $isLocked ? 'pointer-events-none grayscale-[0.5] opacity-80' : '' }}">
                @livewire('predictions.draggable-driver-list', [
                    'drivers' => $drivers,
                    'raceName' => $race ? $race->race_name : 'Race',
                    'season' => $season,
                    'raceRound' => $raceRound,
                    'driverOrder' => $driverOrder,
                    'fastestLapDriverId' => $fastestLapDriverId
                ], key('driver-list-' . $raceRound))
            </div>
        @else
            <div class="space-y-6 {{ $isLocked ? 'pointer-events-none opacity-80' : '' }}">
                @livewire('predictions.draggable-team-list', [
                    'teams' => $teams,
                    'teamOrder' => $teamOrder
                ], key('team-list-' . $season))
                
                {{-- Add Driver Championship prediction list too --}}
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
