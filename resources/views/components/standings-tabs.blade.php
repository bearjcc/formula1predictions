@props(['year'])
@php
    $tabClass = 'border-b-2 py-2 px-1 text-sm font-medium';
    $inactiveClass = 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-red-600 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-red-500';
    $activeClass = 'border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium';
@endphp
<div class="mb-8">
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="flex space-x-8">
            <a href="{{ route('standings.drivers', ['year' => $year]) }}"
               class="{{ $tabClass }} {{ request()->routeIs('standings.drivers') ? $activeClass : $inactiveClass }}"
               wire:navigate>
                {{ __('Driver Standings') }}
            </a>
            <a href="{{ route('standings.teams', ['year' => $year]) }}"
               class="{{ $tabClass }} {{ request()->routeIs('standings.teams') ? $activeClass : $inactiveClass }}"
               wire:navigate>
                {{ __('Team Standings') }}
            </a>
            <a href="{{ route('standings.predictions', ['year' => $year]) }}"
               class="{{ $tabClass }} {{ request()->routeIs('standings.predictions*') ? $activeClass : $inactiveClass }}"
               wire:navigate>
                {{ __('Prediction Standings') }}
            </a>
        </nav>
    </div>
</div>
