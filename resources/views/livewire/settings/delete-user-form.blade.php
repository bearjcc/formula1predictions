<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';
    public bool $confirmingUserDeletion = false;

    public function confirmUserDeletion(): void
    {
        $this->resetErrorBag();
        $this->confirmingUserDeletion = true;
    }

    public function cancelUserDeletion(): void
    {
        $this->confirmingUserDeletion = false;
        $this->password = '';
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $this->confirmingUserDeletion = false;

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h3 class="text-lg font-semibold">{{ __('Delete account') }}</h3>
        <p class="text-zinc-600 dark:text-zinc-400">{{ __('Delete your account and all of its resources') }}</p>
    </div>

    <div>
        <x-mary-button variant="error" wire:click="confirmUserDeletion">
            {{ __('Delete account') }}
        </x-mary-button>
    </div>

    <x-mary-modal wire:model="confirmingUserDeletion" :title="__('Are you sure you want to delete your account?')">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Password') }}</label>
                <x-mary-input wire:model="password" type="password" />
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <x-mary-button type="button" variant="secondary" wire:click="cancelUserDeletion">
                    {{ __('Cancel') }}
                </x-mary-button>
                <x-mary-button variant="error" type="submit">
                    {{ __('Delete account') }}
                </x-mary-button>
            </div>
        </form>
    </x-mary-modal>
</section>
