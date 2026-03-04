<x-layouts.layout :title="__(':year Standings', ['year' => $year])" :headerSubtitle="__('Overview of driver, constructor, and prediction standings for the :year Formula 1 season.', ['year' => $year])">
    <x-standings-tabs :year="$year" />

    <x-mary-card>
        <div class="px-4 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-heading-3">{{ $year }} Standings</h2>
            <p class="mt-1 text-sm text-auto-muted">
                Select a tab above to view detailed driver, constructor, or prediction standings for {{ $year }}.
            </p>
        </div>

        <div class="px-4 sm:px-6 py-6 space-y-4">
            <p class="text-sm text-auto-muted">
                You can jump straight to:
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('standings.drivers', ['year' => $year]) }}" class="btn btn-sm btn-outline">
                    Driver standings
                </a>
                <a href="{{ route('standings.constructors', ['year' => $year]) }}" class="btn btn-sm btn-outline">
                    Constructor standings
                </a>
                <a href="{{ route('standings.predictions', ['year' => $year]) }}" class="btn btn-sm btn-outline">
                    Prediction standings
                </a>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>

