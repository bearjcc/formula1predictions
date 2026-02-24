@props(['year'])
@php
    $tabClass = 'whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium';
    $inactiveClass = 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-red-600 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-red-500';
    $activeClass = 'border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium';
@endphp
<div class="mb-8">
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex gap-x-6 overflow-x-auto" aria-label="{{ __('Standings') }}">
            <a href="{{ route('standings.drivers', ['year' => $year]) }}"
               class="{{ $tabClass }} {{ request()->routeIs('standings.drivers') ? $activeClass : $inactiveClass }}"
               wire:navigate>
                <span class="sm:hidden">{{ __('Drivers') }}</span>
                <span class="hidden sm:inline">{{ __('Driver Standings') }}</span>
            </a>
            <a href="{{ route('standings.constructors', ['year' => $year]) }}"
               class="{{ $tabClass }} {{ request()->routeIs('standings.constructors') ? $activeClass : $inactiveClass }}"
               wire:navigate>
                <span class="sm:hidden">{{ __('Constructors') }}</span>
                <span class="hidden sm:inline">{{ __('Constructor Standings') }}</span>
            </a>
            <a href="{{ route('standings.predictions', ['year' => $year]) }}"
               class="{{ $tabClass }} {{ request()->routeIs('standings.predictions*') ? $activeClass : $inactiveClass }}"
               wire:navigate>
                <span class="sm:hidden">{{ __('Predictions') }}</span>
                <span class="hidden sm:inline">{{ __('Prediction Standings') }}</span>
            </a>
        </nav>
    </div>
</div>
