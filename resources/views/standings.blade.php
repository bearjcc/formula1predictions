<x-layouts.layout :title="$year . ' Standings'" :headerSubtitle="__('Browse driver, team, and prediction standings for the :year season.', ['year' => $year])">
    <x-standings-tabs :year="$year" />

    <!-- Simple explainer below tabs -->
    <x-mary-card class="p-6">
        <h2 class="text-heading-3 mb-2">{{ __('How standings work') }}</h2>
        <p class="text-auto-muted">
            {{ __('Use the tabs above to switch between driver, team, and prediction standings. Each tab shows the full table for the selected season.') }}
        </p>
    </x-mary-card>
</x-layouts.layout>
