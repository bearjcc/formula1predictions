@extends('components.layouts.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-heading-1">Leaderboard</h1>
            <p class="text-zinc-600 dark:text-zinc-400">F1 Predictions Rankings</p>
        </div>
        <a href="{{ route('leaderboard.compare', ['season' => $season]) }}" class="btn btn-outline btn-primary">
            Head-to-Head Compare
        </a>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 mb-8">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Season</span>
                    </label>
                    <select name="season" class="select select-bordered">
                        @foreach($seasons as $s)
                            <option value="{{ $s }}" {{ $season == $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Type</span>
                    </label>
                    <select name="type" class="select select-bordered">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ $type == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="card-title mb-6">
                {{ $types[$type] }} - {{ $season }}
            </h3>

            @if($leaderboard->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Total Score</th>
                                <th>Avg Score</th>
                                <th>Predictions</th>
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
                                                <div class="text-sm opacity-50">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-bold text-lg">{{ $user->total_score ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="text-sm">{{ number_format($user->avg_score ?? 0, 1) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline">{{ $user->predictions_count }}</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ route('leaderboard.user-stats', $user) }}" 
                                               class="btn btn-sm btn-outline">
                                                View Stats
                                            </a>
                                            <a href="{{ route('leaderboard.compare', ['season' => $season, 'users' => $user->id]) }}" 
                                               class="btn btn-sm btn-ghost">
                                                Compare
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-zinc-600 dark:text-zinc-400">No predictions found for the selected criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
