<x-layouts.layout title="Admin Dashboard">
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Manage F1 Predictions system</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title text-primary">{{ $stats['total_users'] }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Users</p>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title text-primary">{{ $stats['total_predictions'] }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Predictions</p>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title text-primary">{{ $stats['pending_predictions'] }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Pending Predictions</p>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title text-primary">{{ $stats['scored_predictions'] }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Scored Predictions</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Predictions -->
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title">Recent Predictions</h3>
                @if($stats['recent_predictions']->count() > 0)
                    <div class="space-y-4">
                        @foreach($stats['recent_predictions'] as $prediction)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                <div>
                                    <p class="font-semibold">{{ $prediction->user->name }}</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ ucfirst($prediction->type) }} - {{ $prediction->season }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold">{{ ucfirst($prediction->status) }}</p>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ $prediction->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-zinc-600 dark:text-zinc-400">No recent predictions</p>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title">Quick Actions</h3>
                <div class="space-y-4">
                    <a href="{{ route('admin.users') }}" class="btn btn-primary w-full">
                        Manage Users
                    </a>
                    <a href="{{ route('admin.predictions') }}" class="btn btn-outline w-full">
                        Manage Predictions
                    </a>
                    <a href="{{ route('admin.races') }}" class="btn btn-outline w-full">
                        Manage Races
                    </a>
                    <a href="{{ route('admin.settings') }}" class="btn btn-outline w-full">
                        System Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.layout>
