@props(['prediction' => null, 'action' => '', 'method' => 'POST'])

<form method="{{ $method }}" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <!-- Prediction Type -->
    <div>
        <label for="type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Prediction Type
        </label>
        <select 
            id="type" 
            name="type" 
            class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-zinc-100 @error('type') border-red-500 @enderror"
            required
        >
            <option value="">Select prediction type</option>
            <option value="race" {{ old('type', $prediction?->type) === 'race' ? 'selected' : '' }}>Race Prediction</option>
            <option value="preseason" {{ old('type', $prediction?->type) === 'preseason' ? 'selected' : '' }}>Preseason Prediction</option>
            <option value="midseason" {{ old('type', $prediction?->type) === 'midseason' ? 'selected' : '' }}>Midseason Prediction</option>
        </select>
        @error('type')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Season -->
    <div>
        <label for="season" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Season
        </label>
        <input 
            type="number" 
            id="season" 
            name="season" 
            value="{{ old('season', $prediction?->season ?? date('Y')) }}"
            min="1950" 
            max="2030"
            class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-zinc-100 @error('season') border-red-500 @enderror"
            required
        >
        @error('season')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Race Round (for race predictions) -->
    <div id="race-round-field" class="hidden">
        <label for="race_round" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Race Round
        </label>
        <input 
            type="number" 
            id="race_round" 
            name="race_round" 
            value="{{ old('race_round', $prediction?->race_round) }}"
            min="1" 
            max="25"
            class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-zinc-100 @error('race_round') border-red-500 @enderror"
        >
        @error('race_round')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Driver Order (for race predictions) -->
    <div id="driver-order-field" class="hidden">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Driver Finishing Order (1st to 20th)
        </label>
        <div class="space-y-2" id="driver-order-list">
            <!-- Driver order will be populated via JavaScript -->
        </div>
        @error('prediction_data.driver_order')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Fastest Lap (for race predictions) -->
    <div id="fastest-lap-field" class="hidden">
        <label for="fastest_lap" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Fastest Lap
        </label>
        <select 
            id="fastest_lap" 
            name="prediction_data[fastest_lap]" 
            class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-zinc-100 @error('prediction_data.fastest_lap') border-red-500 @enderror"
        >
            <option value="">Select driver</option>
            <!-- Driver options will be populated via JavaScript -->
        </select>
        @error('prediction_data.fastest_lap')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Team Order (for preseason/midseason predictions) -->
    <div id="team-order-field" class="hidden">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Team Championship Order (1st to 10th)
        </label>
        <div class="space-y-2" id="team-order-list">
            <!-- Team order will be populated via JavaScript -->
        </div>
        @error('prediction_data.team_order')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Driver Championship Order (for preseason/midseason predictions) -->
    <div id="driver-championship-field" class="hidden">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Driver Championship Order (1st to 20th)
        </label>
        <div class="space-y-2" id="driver-championship-list">
            <!-- Driver championship order will be populated via JavaScript -->
        </div>
        @error('prediction_data.driver_championship')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Superlatives (for preseason/midseason predictions) -->
    <div id="superlatives-field" class="hidden">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Superlatives
        </label>
        <div class="space-y-3">
            <div>
                <label for="most_podiums_team" class="block text-sm text-zinc-600 dark:text-zinc-400">Team with Most Podiums</label>
                <select name="prediction_data[superlatives][most_podiums_team]" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md">
                    <option value="">Select team</option>
                    <!-- Team options will be populated via JavaScript -->
                </select>
            </div>
            <div>
                <label for="most_podiums_driver" class="block text-sm text-zinc-600 dark:text-zinc-400">Driver with Most Podiums</label>
                <select name="prediction_data[superlatives][most_podiums_driver]" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md">
                    <option value="">Select driver</option>
                    <!-- Driver options will be populated via JavaScript -->
                </select>
            </div>
            <div>
                <label for="most_dnfs_team" class="block text-sm text-zinc-600 dark:text-zinc-400">Team with Most DNFs</label>
                <select name="prediction_data[superlatives][most_dnfs_team]" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md">
                    <option value="">Select team</option>
                    <!-- Team options will be populated via JavaScript -->
                </select>
            </div>
            <div>
                <label for="most_dnfs_driver" class="block text-sm text-zinc-600 dark:text-zinc-400">Driver with Most DNFs</label>
                <select name="prediction_data[superlatives][most_dnfs_driver]" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md">
                    <option value="">Select driver</option>
                    <!-- Driver options will be populated via JavaScript -->
                </select>
            </div>
        </div>
        @error('prediction_data.superlatives')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Notes -->
    <div>
        <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Notes (Optional)
        </label>
        <textarea 
            id="notes" 
            name="notes" 
            rows="3"
            class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-zinc-100 @error('notes') border-red-500 @enderror"
            placeholder="Add any additional notes about your prediction..."
        >{{ old('notes', $prediction?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end space-x-3">
        <button 
            type="button" 
            onclick="history.back()" 
            class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            Cancel
        </button>
        <button 
            type="submit" 
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            {{ $prediction ? 'Update Prediction' : 'Create Prediction' }}
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const raceRoundField = document.getElementById('race-round-field');
    const driverOrderField = document.getElementById('driver-order-field');
    const fastestLapField = document.getElementById('fastest-lap-field');
    const teamOrderField = document.getElementById('team-order-field');
    const driverChampionshipField = document.getElementById('driver-championship-field');
    const superlativesField = document.getElementById('superlatives-field');

    function toggleFields() {
        const selectedType = typeSelect.value;
        
        // Hide all fields first
        raceRoundField.classList.add('hidden');
        driverOrderField.classList.add('hidden');
        fastestLapField.classList.add('hidden');
        teamOrderField.classList.add('hidden');
        driverChampionshipField.classList.add('hidden');
        superlativesField.classList.add('hidden');
        
        // Show relevant fields based on type
        if (selectedType === 'race') {
            raceRoundField.classList.remove('hidden');
            driverOrderField.classList.remove('hidden');
            fastestLapField.classList.remove('hidden');
        } else if (selectedType === 'preseason' || selectedType === 'midseason') {
            teamOrderField.classList.remove('hidden');
            driverChampionshipField.classList.remove('hidden');
            superlativesField.classList.remove('hidden');
        }
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields(); // Initial call
});
</script>

