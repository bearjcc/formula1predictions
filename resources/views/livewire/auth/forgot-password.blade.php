<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('Email address') }}
                <span class="text-red-500">*</span>
            </label>
            <x-mary-input
                id="email"
                wire:model="email"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />
        </div>

        <x-mary-button type="submit" class="w-full justify-center">{{ __('Email password reset link') }}</x-mary-button>
    </form>

    <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Or, return to') }}</span>
        <a href="{{ route('login') }}" class="font-medium text-zinc-900 dark:text-zinc-100 hover:text-zinc-700 dark:hover:text-zinc-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800 rounded-md" wire:navigate>
            {{ __('log in') }}
        </a>
    </p>
</div>
