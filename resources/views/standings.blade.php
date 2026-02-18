<x-layouts.layout :title="$year . ' Standings'" :headerSubtitle="__('Browse driver, team, and prediction standings for the :year season.', ['year' => $year])">
    <!-- Navigation Tabs -->
    <div class="mb-8">
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <nav class="flex space-x-8">
                <a href="{{ route('standings.drivers', ['year' => $year]) }}" class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-zinc-500 hover:text-zinc-700 hover:border-red-600 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-red-500" wire:navigate>
                    Driver Standings
                </a>
                <a href="{{ route('standings.teams', ['year' => $year]) }}" class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-zinc-500 hover:text-zinc-700 hover:border-red-600 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-red-500" wire:navigate>
                    Team Standings
                </a>
                <a href="{{ route('standings.predictions', ['year' => $year]) }}" class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-zinc-500 hover:text-zinc-700 hover:border-red-600 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-red-500" wire:navigate>
                    Prediction Standings
                </a>
            </nav>
        </div>
    </div>

    <!-- Simple explainer below tabs -->
    <x-mary-card class="p-6">
        <h2 class="text-heading-3 mb-2">{{ __('How standings work') }}</h2>
        <p class="text-auto-muted">
            {{ __('Use the tabs above to switch between driver, team, and prediction standings. Each tab shows the full table for the selected season.') }}
        </p>
    </x-mary-card>
</x-layouts.layout>
