<div class="relative" x-data="{ open: @entangle('isOpen') }">
    <!-- Notification Bell Button -->
    <button 
        wire:click="toggleDropdown"
        class="relative p-2 text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors"
        @click.away="open = false"
    >
        <x-mary-icon name="o-bell" class="w-5 h-5" />
        
        <!-- Unread Count Badge -->
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notification Dropdown -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-zinc-800 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-700 z-50"
        style="display: none;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                Notifications
            </h3>
            @if($unreadCount > 0)
                <button 
                    wire:click="markAllAsRead"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                >
                    Mark all as read
                </button>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @if($this->notifications->count() > 0)
                @foreach($this->notifications as $notification)
                    <div 
                        class="p-4 border-b border-zinc-100 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors {{ $notification->read_at ? 'opacity-75' : '' }}"
                        wire:key="notification-{{ $notification->id }}"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    @if(!$notification->read_at)
                                        <div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></div>
                                    @endif

                                    @php
                                        $type = $notification->data['type'] ?? null;
                                    @endphp

                                    @if($type === 'prediction_scored')
                                        <div class="flex flex-col">
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                                Prediction scored
                                            </p>
                                            <p class="text-xs text-zinc-600 dark:text-zinc-300 truncate">
                                                {{ $notification->data['race_name'] ?? ($notification->data['season'] ?? 'Season') }}
                                                @if(isset($notification->data['score']))
                                                    &nbsp;&middot;&nbsp;{{ $notification->data['score'] }} pts
                                                @endif
                                                @if(isset($notification->data['accuracy']))
                                                    &nbsp;({{ number_format($notification->data['accuracy'], 1) }}%)
                                                @endif
                                            </p>
                                        </div>
                                    @else
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white truncate">
                                            {{ $notification->data['message'] ?? 'New notification' }}
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="mt-1 flex items-center justify-between">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                    
                                    @if(isset($notification->data['action_url']))
                                        <a 
                                            href="{{ $notification->data['action_url'] }}" 
                                            class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                            wire:navigate
                                        >
                                            @if($type === 'prediction_scored')
                                                View prediction
                                            @else
                                                View
                                            @endif
                                        </a>
                                    @endif
                                </div>
                            </div>
                            
                            @if(!$notification->read_at)
                                <button 
                                    wire:click="markAsRead('{{ $notification->id }}')"
                                    class="ml-2 text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                                    title="Mark as read"
                                >
                                    <x-mary-icon name="o-check" class="w-3 h-3" />
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="p-8 text-center">
                    <x-mary-icon name="o-bell" class="w-8 h-8 text-zinc-400 dark:text-zinc-500 mx-auto mb-2" />
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        No notifications yet
                    </p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        @if($this->notifications->count() > 0)
            <div class="p-3 border-t border-zinc-200 dark:border-zinc-700">
                <a 
                    href="{{ route('notifications.index') }}" 
                    class="block text-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                    wire:navigate
                >
                    View all notifications
                </a>
            </div>
        @endif
    </div>
</div>
