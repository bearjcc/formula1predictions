<x-layouts.layout :title="__(':year Prediction Standings', ['year' => $year])">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 mb-2">{{ $year }} {{ __('Prediction Standings') }}</h1>
                <p class="text-auto-muted">
                    {{ __('Prediction championship standings based on real user prediction scores for the :year season.', ['year' => $year]) }}
                </p>
            </div>
        </div>
    </div>

    <livewire:global-leaderboard :season="$year" />
</x-layouts.layout>
