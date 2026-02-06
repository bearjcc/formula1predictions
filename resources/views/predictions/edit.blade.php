<x-layouts.layout title="Edit Prediction">
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Edit Prediction</h1>
        <p class="text-zinc-600 dark:text-zinc-400">
            Update your prediction for this race.
        </p>
    </div>

    @livewire('predictions.prediction-form', ['existingPrediction' => $prediction, 'race' => $prediction->race])
</x-layouts.layout>
