<?php

use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{computed, on};

$notifications = computed(function () {
    if (!Auth::check()) {
        return collect();
    }

    return Auth::user()
        ->notifications()
        ->latest()
        ->paginate(20);
});

$markAsRead = function (string $notificationId) {
    if (!Auth::check()) {
        return;
    }

    $notification = Auth::user()->notifications()->find($notificationId);
    
    if ($notification && !$notification->read_at) {
        $notification->markAsRead();
    }
};

$markAllAsRead = function () {
    if (!Auth::check()) {
        return;
    }

    Auth::user()->unreadNotifications->markAsRead();
};

$deleteNotification = function (string $notificationId) {
    if (!Auth::check()) {
        return;
    }

    $notification = Auth::user()->notifications()->find($notificationId);
    
    if ($notification) {
        $notification->delete();
    }
};

on(['notification-received' => function () {
    // Refresh the component when a new notification is received
}]);

?>

<div>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Notifications
                    </h1>
                    <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                        Stay updated with your prediction results and race information
                    </p>
                </div>
                
                @if(Auth::user()?->unreadNotifications()->count() > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Mark all as read
                    </button>
                @endif
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            @if($this->notifications->count() > 0)
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->notifications as $notification)
                        <div 
                            class="p-6 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors {{ $notification->read_at ? 'opacity-75' : '' }}"
                            wire:key="notification-{{ $notification->id }}"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start space-x-3">
                                        @if(!$notification->read_at)
                                            <div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></div>
                                        @endif
                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                                    {{ $notification->data['message'] ?? 'New notification' }}
                                                </p>
                                                
                                                <div class="flex items-center space-x-2">
                                                    @if(isset($notification->data['action_url']))
                                                        <a 
                                                            href="{{ $notification->data['action_url'] }}" 
                                                            class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                                            wire:navigate
                                                        >
                                                            View
                                                        </a>
                                                    @endif
                                                    
                                                    @if(!$notification->read_at)
                                                        <button 
                                                            wire:click="markAsRead('{{ $notification->id }}')"
                                                            class="text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                                                            title="Mark as read"
                                                        >
                                                            <x-mary-icon name="o-check" class="w-4 h-4" />
                                                        </button>
                                                    @endif
                                                    
                                                    <button 
                                                        wire:click="deleteNotification('{{ $notification->id }}')"
                                                        class="text-sm text-zinc-400 hover:text-red-600 dark:hover:text-red-400"
                                                        title="Delete notification"
                                                    >
                                                        <x-mary-icon name="o-trash" class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-2">
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $notification->created_at->format('M j, Y \a\t g:i A') }}
                                                    ({{ $notification->created_at->diffForHumans() }})
                                                </p>
                                            </div>
                                            
                                            <!-- Additional notification details -->
                                            @if(isset($notification->data['score']))
                                                <div class="mt-2 flex items-center space-x-4 text-xs text-zinc-600 dark:text-zinc-400">
                                                    <span>Score: {{ $notification->data['score'] }} points</span>
                                                    @if(isset($notification->data['accuracy']))
                                                        <span>Accuracy: {{ number_format($notification->data['accuracy'], 1) }}%</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                @if($this->notifications->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $this->notifications->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <x-mary-icon name="o-bell" class="w-12 h-12 text-zinc-400 dark:text-zinc-500 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">
                        No notifications yet
                    </h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        You'll see notifications here when race results are available or when your predictions are scored.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
