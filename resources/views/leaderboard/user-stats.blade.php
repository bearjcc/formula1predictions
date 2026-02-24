@extends('components.layouts.layout')

@section('title', $user->name . "'s Statistics")
@section('headerSubtitle', 'Prediction performance overview')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex justify-end gap-2">
        <a href="{{ route('leaderboard.compare', ['season' => $stats['season_stats']->first()?->season ?? date('Y'), 'users' => $user->id]) }}" class="btn btn-outline">
            Head-to-Head Compare
        </a>
        <a href="{{ route('leaderboard.index') }}" class="btn btn-outline">
            Back to Leaderboard
        </a>
    </div>

    <!-- User Info -->
    <div class="card bg-base-100 mb-8">
        <div class="card-body">
            <div class="flex items-center space-x-4">
                <div class="avatar">
                    <div class="mask mask-squircle w-16 h-16">
                        <div class="bg-primary text-primary-content rounded-lg flex items-center justify-center">
                            <span class="text-2xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold">{{ $user->name }}</h3>
                    <p class="text-sm text-zinc-500">Member since {{ $user->created_at->format('M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card bg-base-100">
            <div class="card-body text-center">
                <h3 class="card-title text-primary text-2xl">{{ $stats['total_predictions'] }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Predictions</p>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body text-center">
                <h3 class="card-title text-primary text-2xl">{{ $stats['total_score'] }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Score</p>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body text-center">
                <h3 class="card-title text-primary text-2xl">{{ $stats['avg_score'] }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Average Score</p>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body text-center">
                <h3 class="card-title text-primary text-2xl">{{ $stats['accuracy'] }}%</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Accuracy</p>
            </div>
        </div>
    </div>

    <!-- Season Breakdown -->
    @if($stats['season_stats']->count() > 0)
        <div class="card bg-base-100 mb-8">
            <div class="card-body">
                <h3 class="card-title mb-6">Season Breakdown</h3>
                <div class="w-full max-w-full min-w-0 overflow-x-auto [-webkit-overflow-scrolling:touch]">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Season</th>
                                <th>Predictions</th>
                                <th>Total Score</th>
                                <th>Avg Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['season_stats'] as $seasonStat)
                                <tr>
                                    <td>
                                        <a href="{{ route('leaderboard.season', $seasonStat->season) }}" 
                                           class="link link-primary">
                                            {{ $seasonStat->season }}
                                        </a>
                                    </td>
                                    <td>{{ $seasonStat->predictions }}</td>
                                    <td>{{ $seasonStat->total_score }}</td>
                                    <td>{{ number_format($seasonStat->avg_score, 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Predictions -->
    @if($recentPredictions->count() > 0)
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title mb-6">Recent Predictions</h3>
                <div class="space-y-4">
                    @foreach($recentPredictions as $prediction)
                        <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                            <div>
                                <p class="font-semibold">
                                    {{ ucfirst($prediction->type) }} - {{ $prediction->season }}
                                    @if($prediction->race_round)
                                        (Round {{ $prediction->race_round }})
                                    @endif
                                </p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $prediction->created_at->format('M j, Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">{{ ucfirst($prediction->status) }}</p>
                                @if($prediction->score)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Score: {{ $prediction->score }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
