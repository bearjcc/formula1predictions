<x-layouts.layout title="Make Prediction">
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Create Prediction</h1>
        <p class="text-zinc-600 dark:text-zinc-400">
            Submit your predicted finishing order and fastest lap.
        </p>
    </div>

    @livewire('predictions.prediction-form', ['race' => $race])
</x-layouts.layout>
