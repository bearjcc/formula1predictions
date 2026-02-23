{{-- Race schedule page with LiveWire integration --}}
{{-- Features: filtering, search, loading states, responsive design --}}
{{-- Integrated with F1 API for real-time race data --}}

<x-layouts.layout :title="$year . ' Races'" :headerSubtitle="'View the complete race schedule for the ' . $year . ' Formula 1 season'">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-calendar">
                    Calendar View
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Livewire Races Component -->
    @livewire('races.races-list', ['year' => $year])
</x-layouts.layout>
