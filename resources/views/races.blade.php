{{-- Race schedule page with LiveWire integration --}}
{{-- Features: filtering, search, loading states, responsive design --}}
{{-- Integrated with F1 API for real-time race data --}}

<x-layouts.layout>
    <!-- Page Header: theme-safe text for light/dark (F1-083) -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 mb-2 text-zinc-900 dark:text-zinc-100">{{ $year }} Races</h1>
                <p class="text-auto-muted text-zinc-600 dark:text-zinc-400">
                    View the complete race schedule for the {{ $year }} Formula 1 season
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-calendar">
                    Calendar View
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-plus">
                    Add Race
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Livewire Races Component -->
    @livewire('races.races-list', ['year' => $year])
</x-layouts.layout>
