<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SeasonSupporter extends Component
{
    public User $user;
    public bool $isSupporter = false;
    public string $supporterSince = '';
    public bool $showModal = false;
    public bool $confirming = false;

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->isSupporter = $this->user->is_season_supporter;
        $this->supporterSince = $this->user->supporter_since ? $this->user->supporter_since->format('F j, Y') : '';
    }

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->confirming = false;
    }

    public function confirmSupport(): void
    {
        $this->confirming = true;
    }

    public function becomeSupporter(): void
    {
        $success = $this->user->makeSeasonSupporter();
        
        if ($success) {
            $this->isSupporter = true;
            $this->supporterSince = now()->format('F j, Y');
            $this->closeModal();
            $this->dispatch('supporter-updated');
        }
    }

    public function render()
    {
        return view('livewire.season-supporter');
    }
}
