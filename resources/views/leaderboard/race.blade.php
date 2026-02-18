@extends('components.layouts.layout')

@section('title', $race->race_name . ' Leaderboard')
@section('headerSubtitle', 'Round ' . $raceRound . ' - ' . $season . ' Season')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex flex-wrap items-center justify-end gap-2">
        <a href="{{ route('leaderboard.season', $season) }}" class="btn btn-outline">
            Season Leaderboard
        </a>
        <a href="{{ route('leaderboard.index') }}" class="btn btn-outline">
            All Seasons
        </a>
    </div>

    <!-- Race Leaderboard Table -->
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="card-title mb-6">Race Prediction Rankings</h3>

            @if($leaderboard->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaderboard as $user)
                                <tr>
                                    <td>
                                        @if($user->rank <= 3)
                                            <div class="flex items-center">
                                                @if($user->rank == 1)
                                                    <span class="text-yellow-500">🥇</span>
                                                @elseif($user->rank == 2)
                                                    <span class="text-gray-400">🥈</span>
                                                @elseif($user->rank == 3)
                                                    <span class="text-amber-600">🥉</span>
                                                @endif
                                                <span class="ml-2 font-bold">{{ $user->rank }}</span>
                                            </div>
                                        @else
                                            <span class="font-bold">{{ $user->rank }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center space-x-3">
                                            <div class="avatar">
                                                <div class="mask mask-squircle w-12 h-12">
                                                    <div class="bg-primary text-primary-content rounded-lg flex items-center justify-center">
                                                        <span class="text-lg font-bold">
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-bold text-lg">{{ $user->score ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('leaderboard.user-stats', $user) }}"
                                           class="btn btn-sm btn-outline">
                                            View Stats
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-zinc-600 dark:text-zinc-400">No predictions have been scored for this race yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
