@php
    $status = $race['status'] ?? 'upcoming';
    $circuit = is_array($race['circuit'] ?? null) ? $race['circuit'] : [];
    $badgeClass = match ($status) {
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'upcoming' => 'border-blue-200 text-blue-700 dark:border-blue-700 dark:text-blue-300',
        'ongoing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        default => 'border-zinc-200 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300',
    };
@endphp
<div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center space-x-3 mb-3">
                <x-mary-badge variant="outline" class="{{ $badgeClass }}">
                    {{ ucfirst($status) }}
                </x-mary-badge>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $race['raceName'] ?? 'Race' }}</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="o-map-pin" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                    <span class="text-zinc-700 dark:text-zinc-300">{{ $circuit['circuitName'] ?? 'TBA circuit' }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="o-calendar" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                    <span class="text-zinc-700 dark:text-zinc-300">
                        @if(!empty($race['date']))
                            @php
                                try {
                                    $dateStr = \Carbon\Carbon::parse($race['date'])->format('M j, Y');
                                } catch (\Throwable) {
                                    $dateStr = 'TBA';
                                }
                            @endphp
                            {{ $dateStr }}
                        @else
                            TBA
                        @endif
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="o-flag" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                    <span class="text-zinc-700 dark:text-zinc-300">{{ $circuit['country'] ?? 'TBA' }}</span>
                </div>
            </div>

            @if(!empty($circuit['circuitLength']))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="flex items-center space-x-2">
                        <x-mary-icon name="o-map" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-zinc-700 dark:text-zinc-300">Length: {{ $circuit['circuitLength'] }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-mary-icon name="o-clock" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-zinc-700 dark:text-zinc-300">
                            @if(!empty($race['time']))
                                @php
                                    try {
                                        $timeStr = \Carbon\Carbon::parse($race['time'])->format('H:i');
                                    } catch (\Throwable) {
                                        $timeStr = 'TBA';
                                    }
                                @endphp
                                Time: {{ $timeStr }}
                            @else
                                Time: TBA
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-mary-icon name="o-trophy" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-zinc-700 dark:text-zinc-300">Round {{ $race['round'] ?? '?' }}</span>
                    </div>
                </div>
            @endif

            <div class="flex items-center space-x-4">
                <x-mary-button variant="outline" size="sm" icon="o-eye">
                    View Details
                </x-mary-button>
                @if(($race['status'] ?? '') === 'completed' && !empty($race['results']))
                    <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                        Results ({{ count($race['results']) }} drivers)
                    </x-mary-button>
                @endif
                @if(($race['status'] ?? '') === 'upcoming')
                    <x-mary-button
                        variant="primary"
                        size="sm"
                        icon="o-plus"
                        wire:click="makePrediction({{ (int) ($race['round'] ?? 0) }})"
                    >
                        Make Prediction
                    </x-mary-button>
                @endif
                <x-mary-button variant="outline" size="sm" icon="o-users">
                    Predictions
                </x-mary-button>
            </div>
        </div>

        <div class="flex flex-col items-end space-y-2">
            <x-mary-button variant="ghost" size="sm" icon="o-star">
                Favorite
            </x-mary-button>
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button">
                    <x-mary-button variant="ghost" size="sm" icon="o-ellipsis-vertical" />
                </div>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                    <li><a class="flex items-center space-x-2"><x-mary-icon name="o-pencil" class="w-4 h-4" /><span>Edit</span></a></li>
                    <li><a class="flex items-center space-x-2"><x-mary-icon name="o-document-duplicate" class="w-4 h-4" /><span>Duplicate</span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="flex items-center space-x-2 text-red-600"><x-mary-icon name="o-trash" class="w-4 h-4" /><span>Delete</span></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
