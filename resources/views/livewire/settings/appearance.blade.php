<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $appearance = 'system';

    public function mount(): void
    {
        $this->appearance = session('appearance', config('f1.default_appearance', 'system'));
    }

    public function updateAppearance(string $value): void
    {
        $this->appearance = $value;
        session(['appearance' => $value]);
        
        // Dispatch event to update the theme
        $this->dispatch('appearance-changed', appearance: $value);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div class="space-y-4">
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" wire:model.live="appearance" value="light" class="form-radio">
                    <x-mary-icon name="o-sun" class="w-4 h-4" />
                    <span>{{ __('Light') }}</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" wire:model.live="appearance" value="dark" class="form-radio">
                    <x-mary-icon name="o-moon" class="w-4 h-4" />
                    <span>{{ __('Dark') }}</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" wire:model.live="appearance" value="system" class="form-radio">
                    <x-mary-icon name="o-computer-desktop" class="w-4 h-4" />
                    <span>{{ __('System') }}</span>
                </label>
            </div>
        </div>
    </x-settings.layout>
</section>
