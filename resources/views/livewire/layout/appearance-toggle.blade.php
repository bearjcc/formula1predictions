<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $appearance = 'system';

    public function mount(): void
    {
        $this->appearance = session('appearance', config('f1.default_appearance', 'system'));
    }

    public function setAppearance(string $value): void
    {
        $this->appearance = $value;
        session(['appearance' => $value]);
        $this->dispatch('appearance-changed', appearance: $value);
    }
}; ?>

<div class="relative" x-data="{ open: false }">
    <button type="button"
        @click="open = !open"
        @click.away="open = false"
        class="relative p-2 text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors"
        aria-label="{{ __('Appearance') }}"
        aria-haspopup="true"
        :aria-expanded="open"
    >
        @if($appearance === 'light')
            <x-mary-icon name="o-sun" class="w-5 h-5" />
        @elseif($appearance === 'dark')
            <x-mary-icon name="o-moon" class="w-5 h-5" />
        @else
            <x-mary-icon name="o-computer-desktop" class="w-5 h-5" />
        @endif
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-48 py-1 bg-white dark:bg-zinc-800 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-700 z-50"
        style="display: none;"
    >
        <button type="button"
            wire:click="setAppearance('light')"
            @click="open = false"
            class="flex items-center gap-2 w-full px-4 py-2 text-left text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 {{ $appearance === 'light' ? 'bg-zinc-50 dark:bg-zinc-700/50 font-medium' : '' }}"
        >
            <x-mary-icon name="o-sun" class="w-4 h-4 shrink-0" />
            <span>{{ __('Light') }}</span>
        </button>
        <button type="button"
            wire:click="setAppearance('dark')"
            @click="open = false"
            class="flex items-center gap-2 w-full px-4 py-2 text-left text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 {{ $appearance === 'dark' ? 'bg-zinc-50 dark:bg-zinc-700/50 font-medium' : '' }}"
        >
            <x-mary-icon name="o-moon" class="w-4 h-4 shrink-0" />
            <span>{{ __('Dark') }}</span>
        </button>
        <button type="button"
            wire:click="setAppearance('system')"
            @click="open = false"
            class="flex items-center gap-2 w-full px-4 py-2 text-left text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 {{ $appearance === 'system' ? 'bg-zinc-50 dark:bg-zinc-700/50 font-medium' : '' }}"
        >
            <x-mary-icon name="o-computer-desktop" class="w-4 h-4 shrink-0" />
            <span>{{ __('System') }}</span>
        </button>
    </div>
</div>
