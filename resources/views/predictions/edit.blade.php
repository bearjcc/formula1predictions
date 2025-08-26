@extends('components.layouts.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Edit Prediction</h1>

        <form action="{{ route('predictions.update', $prediction) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="form-control">
                <label for="type" class="label">
                    <span class="label-text">Prediction Type</span>
                </label>
                <select name="type" id="type" class="select select-bordered" required>
                    <option value="">Select type...</option>
                    <option value="race" {{ $prediction->type === 'race' ? 'selected' : '' }}>Race Prediction</option>
                    <option value="preseason" {{ $prediction->type === 'preseason' ? 'selected' : '' }}>Preseason Prediction</option>
                    <option value="midseason" {{ $prediction->type === 'midseason' ? 'selected' : '' }}>Midseason Prediction</option>
                </select>
                @error('type')
                    <div class="text-error text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-control">
                <label for="season" class="label">
                    <span class="label-text">Season</span>
                </label>
                <input type="number" name="season" id="season" class="input input-bordered" 
                       value="{{ old('season', $prediction->season) }}" min="2022" max="2024" required>
                @error('season')
                    <div class="text-error text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-control" id="race-round-field" style="display: {{ $prediction->type === 'race' ? 'block' : 'none' }};">
                <label for="race_round" class="label">
                    <span class="label-text">Race Round</span>
                </label>
                <input type="number" name="race_round" id="race_round" class="input input-bordered" 
                       value="{{ old('race_round', $prediction->race_round) }}" min="1" max="24">
                @error('race_round')
                    <div class="text-error text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-control">
                <label for="notes" class="label">
                    <span class="label-text">Notes (Optional)</span>
                </label>
                <textarea name="notes" id="notes" class="textarea textarea-bordered" 
                          rows="3" maxlength="1000">{{ old('notes', $prediction->notes) }}</textarea>
                @error('notes')
                    <div class="text-error text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">Update Prediction</button>
                <a href="{{ route('predictions.show', $prediction) }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('type').addEventListener('change', function() {
    const raceRoundField = document.getElementById('race-round-field');
    if (this.value === 'race') {
        raceRoundField.style.display = 'block';
        document.getElementById('race_round').required = true;
    } else {
        raceRoundField.style.display = 'none';
        document.getElementById('race_round').required = false;
    }
});
</script>
@endsection
