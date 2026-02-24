<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.layout', ['title' => 'Forgot password', 'headerSubtitle' => 'Enter your email to receive a password reset link'])] class extends Component {
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

<div class="mx-auto w-full max-w-md min-w-0">
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 shadow-sm min-w-0">
        <x-auth-session-status class="mb-4 text-center text-sm" :status="session('status')" />

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

    <p class="mt-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Or, return to') }}</span>
        <a href="{{ route('login') }}" class="font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 rounded-md" wire:navigate>
            {{ __('log in') }}
        </a>
    </p>
    </div>
</div>
