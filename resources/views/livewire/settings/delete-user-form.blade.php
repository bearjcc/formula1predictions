<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

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
        <x-mary-button variant="error" onclick="deleteModal.showModal()">
            {{ __('Delete account') }}
        </x-mary-button>
    </div>

    <dialog id="deleteModal" class="modal">
        <div class="modal-box">
            <form method="POST" wire:submit="deleteUser" class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold">{{ __('Are you sure you want to delete your account?') }}</h3>

                    <p class="text-zinc-600 dark:text-zinc-400 mt-2">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Password') }}</label>
                    <x-mary-input wire:model="password" type="password" />
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <button type="button" class="btn btn-outline" onclick="deleteModal.close()">{{ __('Cancel') }}</button>
                    <x-mary-button variant="error" type="submit">{{ __('Delete account') }}</x-mary-button>
                </div>
            </form>
        </div>
    </dialog>
</section>
