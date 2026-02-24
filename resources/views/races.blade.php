{{-- Race schedule page with LiveWire integration --}}
{{-- Features: filtering, search, loading states, responsive design --}}
{{-- Integrated with F1 API for real-time race data --}}

<x-layouts.layout :title="$year . ' Races'" :headerSubtitle="'View the complete race schedule for the ' . $year . ' Formula 1 season'">
    <!-- Livewire Races Component (includes List / Calendar view toggle) -->
    @livewire('races.races-list', ['year' => $year])
</x-layouts.layout>
