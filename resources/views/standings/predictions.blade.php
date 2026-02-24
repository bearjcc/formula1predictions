<x-layouts.layout :title="__(':year Prediction Standings', ['year' => $year])" :headerSubtitle="__('Prediction championship standings based on real user prediction scores for the :year season.', ['year' => $year])">
    <x-standings-tabs :year="$year" />

    <div class="mb-6 flex flex-wrap items-center gap-3 justify-between">
        <p class="text-sm text-auto-muted">
            This page shows how everyone&rsquo;s prediction scores compare for the selected season. For more views, use the Leaderboard section.
        </p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('leaderboard.index', ['season' => $year]) }}" class="btn btn-outline btn-sm" wire:navigate>
                Open leaderboard
            </a>
            @auth
                <a href="{{ route('predictions.index') }}" class="btn btn-sm" wire:navigate>
                    View your predictions
                </a>
            @endauth
        </div>
    </div>

    <livewire:global-leaderboard :season="$year" />
</x-layouts.layout>
