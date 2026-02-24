<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.layout', ['title' => 'Log in to your account', 'headerSubtitle' => 'Enter your email and password below to log in'])] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="mx-auto w-full max-w-md min-w-0">
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 shadow-sm min-w-0">
        <x-auth-session-status class="mb-4 text-center text-sm" :status="session('status')" />

        <form method="POST" wire:submit="login" class="flex flex-col gap-6">
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
            <div class="relative">
                <x-mary-input
                    id="password"
                    wire:model="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
            </div>
        </div>

        <!-- Remember Me -->
        <div class="flex flex-wrap items-center justify-between gap-2">
            <x-mary-checkbox wire:model="remember" :label="__('Remember me')" />

            @if (Route::has('password.request'))
                <a class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 rounded-md" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <x-mary-button type="submit" class="w-full justify-center">
            {{ __('Log in') }}
        </x-mary-button>

        <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __("Don't have an account?") }}
            <a href="{{ route('register') }}" class="font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 rounded-md" wire:navigate>
                {{ __('Sign up') }}
            </a>
        </p>
    </form>
    </div>
</div>
