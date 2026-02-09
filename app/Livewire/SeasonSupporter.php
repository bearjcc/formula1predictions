<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SeasonSupporter extends Component
{
    public User $user;

    public bool $isSupporter = false;

    public string $supporterSince = '';

    public string $price = '$10';

    public bool $stripeEnabled = false;

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->isSupporter = (bool) ($this->user->is_season_supporter ?? false);
        $this->supporterSince = $this->user->supporter_since ? $this->user->supporter_since->format('F j, Y') : '';

        // Check if Stripe is configured
        $this->stripeEnabled = ! empty(config('services.stripe.secret')) &&
                           ! empty(config('services.stripe.key')) &&
                           config('services.stripe.key') !== 'pk_test_placeholder';
    }

    public function render()
    {
        return view('livewire.season-supporter');
    }
}
