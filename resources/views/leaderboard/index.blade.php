<x-layouts.layout title="Prediction Leaderboard" headerSubtitle="Season-wide prediction standings, filters, and comparison tools in one place.">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-auto-muted">
                Use this page as the single place to track prediction rankings for {{ $season }}.
            </p>
            <x-mary-button
                link="{{ route('leaderboard.compare', ['season' => $season]) }}"
                variant="outline"
                label="Head-to-Head Compare"
            />
        </div>

        <livewire:global-leaderboard :season="$season" />
    </div>
</x-layouts.layout>
