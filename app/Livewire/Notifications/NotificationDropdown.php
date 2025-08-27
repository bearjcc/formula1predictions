<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public bool $isOpen = false;

    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->updateUnreadCount();
    }

    public function toggleDropdown(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function closeDropdown(): void
    {
        $this->isOpen = false;
    }

    public function markAsRead(string $notificationId): void
    {
        if (! Auth::check()) {
            return;
        }

        $notification = Auth::user()->notifications()->find($notificationId);

        if ($notification && ! $notification->read_at) {
            $notification->markAsRead();
            $this->updateUnreadCount();
        }
    }

    public function markAllAsRead(): void
    {
        if (! Auth::check()) {
            return;
        }

        Auth::user()->unreadNotifications->markAsRead();
        $this->updateUnreadCount();
    }

    public function getNotificationsProperty()
    {
        if (! Auth::check()) {
            return collect();
        }

        return Auth::user()
            ->notifications()
            ->latest()
            ->limit(10)
            ->get();
    }

    public function getUnreadNotificationsProperty()
    {
        if (! Auth::check()) {
            return collect();
        }

        return Auth::user()
            ->unreadNotifications()
            ->latest()
            ->limit(5)
            ->get();
    }

    private function updateUnreadCount(): void
    {
        if (! Auth::check()) {
            $this->unreadCount = 0;

            return;
        }

        $this->unreadCount = Auth::user()->unreadNotifications()->count();
    }

    #[On('notification-received')]
    public function handleNewNotification(): void
    {
        $this->updateUnreadCount();
    }

    public function render(): View
    {
        return view('livewire.notifications.notification-dropdown');
    }
}
