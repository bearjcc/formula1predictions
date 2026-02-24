<x-layouts.layout title="Dashboard" headerSubtitle="Your prediction overview">
    {{-- #region Page Header --}}
    <div class="mb-8 flex justify-end">
        <div class="flex items-center gap-3">
            <x-mary-button variant="primary" size="sm" icon="o-plus" link="{{ route('predict.create') }}" wire:navigate>
                Make Prediction
            </x-mary-button>
        </div>
    </div>

    {{-- #region Stats Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-mary-card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-auto-muted">Total Predictions</p>
                    <h2 class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['total_predictions'] }}</h2>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-chart-bar" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </x-mary-card>

        <x-mary-card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-auto-muted">Prediction Accuracy</p>
                    <h2 class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['accuracy'] }}%</h2>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-chart-bar" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </x-mary-card>

        <x-mary-card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-auto-muted">Total Points</p>
                    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['total_score']) }}</h2>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-trophy" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
        </x-mary-card>

        <x-mary-card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-auto-muted">Current Rank</p>
                    <h2 class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $userRank ? '#' . $userRank : '-' }}</h2>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-star" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                </div>
            </div>
        </x-mary-card>
    </div>

    {{-- #region Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        {{-- Upcoming Races --}}
        <x-mary-card class="lg:col-span-2 p-6">
            <h3 class="text-heading-3 mb-4">Upcoming Races</h3>
            @if($preseasonDeadline && !$hasPreseasonPrediction && $preseasonDeadline->isFuture())
                <div class="mb-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg flex items-center justify-between gap-3">
                    <div>
                        <h4 class="font-semibold">Preseason prediction</h4>
                        <p class="text-sm text-auto-muted mt-1">
                            Due at same time as first race.
                            @if($firstRace)
                                Closes {{ $preseasonDeadline->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                    <x-mary-button variant="outline" size="sm" icon="o-pencil-square" link="{{ route('predict.preseason', ['year' => $season]) }}" wire:navigate>
                        Make prediction
                    </x-mary-button>
                </div>
            @endif
            @if($upcomingRaces->isNotEmpty())
                <div class="space-y-4">
                    @foreach($upcomingRaces as $race)
                        @php
                            $deadline = $race->getRacePredictionDeadline();
                            $allowsPredictions = $race->allowsPredictions();
                        @endphp
                        <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                    <x-mary-icon name="o-flag" class="w-6 h-6 text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <h4 class="font-semibold">{{ $race->display_name }}</h4>
                                    <p class="text-sm text-auto-muted">
                                        {{ $race->date?->format('M j, Y') }}
                                        @if($race->locality)
                                            - {{ $race->locality }}
                                        @endif
                                    </p>
                                    @if($deadline && $allowsPredictions)
                                        <p class="text-xs text-zinc-500 mt-1">Predictions close {{ $deadline->diffForHumans() }}</p>
                                    @elseif($deadline && !$allowsPredictions)
                                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Predictions closed</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-mary-button variant="outline" size="sm" icon="o-eye" link="{{ route('race', ['year' => $race->season, 'id' => $race->id]) }}" wire:navigate>
                                    View
                                </x-mary-button>
                                @if($allowsPredictions)
                                    <x-mary-button variant="primary" size="sm" icon="o-plus" link="{{ route('predict.create', ['race_id' => $race->id]) }}" wire:navigate>
                                        Predict
                                    </x-mary-button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No upcoming races for {{ $season }}. Check back later.</p>
            @endif
        </x-mary-card>

        {{-- Leaderboard --}}
        <x-mary-card class="p-6">
            <h3 class="text-heading-3 mb-4">Leaderboard</h3>
            @if($leaderboard->isNotEmpty())
                <div class="space-y-3">
                    @foreach($leaderboard as $entry)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                            <div class="flex items-center space-x-3">
                                @php
                                    $badgeClass = match($entry->rank) {
                                        1 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        2 => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-900 dark:text-zinc-200',
                                        3 => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                        default => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                    };
                                @endphp
                                <x-mary-badge class="{{ $badgeClass }}">{{ $entry->rank }}</x-mary-badge>
                                <div>
                                    <h4 class="font-semibold">{{ $entry->id === auth()->id() ? 'You' : $entry->name }}</h4>
                                    <p class="text-sm text-auto-muted">{{ number_format($entry->total_score ?? 0) }} pts</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <x-mary-button variant="ghost" size="sm" class="mt-3 w-full" link="{{ route('leaderboard.index', ['season' => $season]) }}" wire:navigate>View full leaderboard</x-mary-button>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No leaderboard data for {{ $season }} yet.</p>
            @endif
        </x-mary-card>
    </div>

    {{-- #region Recent Predictions --}}
    <x-mary-card class="p-6 mb-8">
        <h3 class="text-heading-3 mb-4">Recent Predictions</h3>
        @if($recentPredictions->isNotEmpty())
            <div class="space-y-4">
                @foreach($recentPredictions as $prediction)
                    <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <div class="w-10 h-10 {{ $prediction->status === 'scored' ? 'bg-green-100 dark:bg-green-900' : 'bg-blue-100 dark:bg-blue-900' }} rounded-full flex items-center justify-center">
                            <x-mary-icon name="{{ $prediction->status === 'scored' ? 'o-check' : 'o-clock' }}" class="w-5 h-5 {{ $prediction->status === 'scored' ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold">{{ ucfirst($prediction->type) }} - {{ $prediction->race?->display_name ?? ($prediction->type === 'preseason' ? $prediction->season . ' Season' : 'Unknown race') }}</h4>
                            <p class="text-sm text-auto-muted">
                                {{ $prediction->season }} Season
                                @if($prediction->status === 'scored' && $prediction->score !== null)
                                    - {{ $prediction->score }} pts
                                @else
                                    - {{ ucfirst($prediction->status) }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($prediction->isEditable())
                                <x-mary-button variant="ghost" size="sm" icon="o-pencil" link="{{ route('predictions.edit', $prediction) }}" wire:navigate>Edit</x-mary-button>
                            @endif
                            <x-mary-button variant="ghost" size="sm" icon="o-eye" link="{{ route('predictions.show', $prediction) }}" wire:navigate>View</x-mary-button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-zinc-500 dark:text-zinc-400">No predictions yet. Make your first prediction to get started.</p>
        @endif
    </x-mary-card>

    {{-- #region Quick Actions --}}
    <x-mary-card class="p-6">
        <h3 class="text-heading-3 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-mary-button variant="primary" size="lg" icon="o-plus" class="h-20" link="{{ route('predict.create') }}" wire:navigate>
                Make Prediction
            </x-mary-button>
            <x-mary-button variant="outline" size="lg" icon="o-trophy" class="h-20" link="{{ route('leaderboard.index', ['season' => $season]) }}" wire:navigate>
                Leaderboard
            </x-mary-button>
            <x-mary-button variant="outline" size="lg" icon="o-calendar" class="h-20" link="{{ route('races', ['year' => $season]) }}" wire:navigate>
                Race Schedule
            </x-mary-button>
        </div>
    </x-mary-card>
</x-layouts.layout>
