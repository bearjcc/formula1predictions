<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.layout', ['title' => 'Create an account', 'headerSubtitle' => 'Enter your details below to create your account'])] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="mx-auto w-full max-w-md min-w-0">
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 shadow-sm min-w-0">
        <x-auth-session-status class="mb-4 text-center text-sm" :status="session('status')" />

        <form method="POST" wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <div class="space-y-2">
            <label for="name" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('Name') }}
                <span class="text-red-500">*</span>
            </label>
            <x-mary-input
                id="name"
                wire:model="name"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Full name"
            />
        </div>

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
                autocomplete="email"
                placeholder="email@example.com"
            />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <label for="password" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('Password') }}
                <span class="text-red-500">*</span>
            </label>
            <x-mary-input
                id="password"
                wire:model="password"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Password"
            />
        </div>

        <!-- Confirm Password -->
        <div class="space-y-2">
            <label for="password_confirmation" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('Confirm password') }}
                <span class="text-red-500">*</span>
            </label>
            <x-mary-input
                id="password_confirmation"
                wire:model="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Confirm password"
            />
        </div>

        <x-mary-button type="submit" class="w-full justify-center">
            {{ __('Create account') }}
        </x-mary-button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <a href="{{ route('login') }}" class="font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 rounded-md" wire:navigate>
            {{ __('Log in') }}
        </a>
    </p>
    </div>
</div>
