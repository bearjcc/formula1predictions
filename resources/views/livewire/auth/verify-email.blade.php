<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.layout', ['title' => 'Verify your email', 'headerSubtitle' => 'We sent a verification link to your email address.'])] class extends Component {
    /**
     * Send an email verification notification to the user.
     * Catches mail/send failures so legacy or misconfigured setups show a message instead of 500.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        try {
            Auth::user()->sendEmailVerificationNotification();
            Session::flash('status', 'verification-link-sent');
        } catch (\Throwable $e) {
            report($e);
            Session::flash('status', 'verification-link-failed');
        }
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mx-auto w-full max-w-md min-w-0">
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 shadow-sm min-w-0">
        <p class="text-zinc-700 dark:text-zinc-300 break-words">
            {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <p class="mt-4 font-medium text-green-600 dark:text-green-400 break-words">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </p>
        @elseif (session('status') == 'verification-link-failed')
            <p class="mt-4 font-medium text-amber-600 dark:text-amber-400 break-words">
                {{ __('We could not send the verification email. Please try again later or contact support.') }}
            </p>
        @endif

        <div class="mt-6 flex flex-col gap-3">
            <x-mary-button wire:click="sendVerification" class="w-full justify-center">
                {{ __('Resend verification email') }}
            </x-mary-button>

            <button type="button" class="text-sm cursor-pointer text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300" wire:click="logout">
                {{ __('Log out') }}
            </button>
        </div>
    </div>
</div>
