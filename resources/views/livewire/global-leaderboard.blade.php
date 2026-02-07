<div class="space-y-6">
    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title mb-4">Global Leaderboard</h2>
            
            <div class="flex flex-wrap gap-4 items-end">
                <div class="form-control flex-1 min-w-[150px]">
                    <label class="label">
                        <span class="label-text">Season</span>
                    </label>
                    <select wire:model.live="season" class="select select-bordered">
                        @foreach($availableSeasons as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control flex-1 min-w-[150px]">
                    <label class="label">
                        <span class="label-text">Type</span>
                    </label>
                    <select wire:model.live="type" class="select select-bordered">
                        <option value="all">All Predictions</option>
                        <option value="race">Race Predictions</option>
                        <option value="preseason">Preseason</option>
                    </select>
                </div>

                <div class="form-control flex-1 min-w-[150px]">
                    <label class="label">
                        <span class="label-text">Sort By</span>
                    </label>
                    <select wire:model.live="sortBy" class="select select-bordered">
                        <option value="total_score">Total Score</option>
                        <option value="avg_score">Average Score</option>
                        <option value="avg_accuracy">Accuracy</option>
                        <option value="predictions_count">Predictions</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Pro Stats Summary -->
    @if(!empty($proStats))
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <span class="text-primary">Pro Stats</span> - {{ $season }} Season
                </h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="stat">
                        <div class="stat-figure text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="stat-title">Active Users</div>
                        <div class="stat-value text-primary">{{ $proStats['total_users'] }}</div>
                        <div class="stat-desc">Making predictions</div>
                    </div>

                    <div class="stat">
                        <div class="stat-figure text-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="stat-title">Avg Total Score</div>
                        <div class="stat-value text-secondary">{{ $proStats['avg_total_score'] }}</div>
                        <div class="stat-desc">Median: {{ $proStats['median_score'] }}</div>
                    </div>

                    <div class="stat">
                        <div class="stat-figure text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="stat-title">Avg Accuracy</div>
                        <div class="stat-value text-accent">{{ $proStats['avg_accuracy'] }}%</div>
                        <div class="stat-desc">Community average</div>
                    </div>

                    <div class="stat">
                        <div class="stat-figure text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div class="stat-title">Perfect Predictions</div>
                        <div class="stat-value text-info">{{ $proStats['perfect_predictions'] }}</div>
                        <div class="stat-desc">{{ $proStats['supporters'] }} supporters</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Leaderboard Table -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            @if(!empty($leaderboard))
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Total Score</th>
                                <th>Avg Score</th>
                                <th>Accuracy</th>
                                <th>Predictions</th>
                                <th>Perfect</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaderboard as $user)
                                <tr class="@if($user['is_supporter']) border-l-4 border-yellow-500 @endif">
                                    <td>
                                        @if($user['rank'] <= 3)
                                            <div class="flex items-center">
                                                @if($user['rank'] == 1)
                                                    <span class="text-2xl">🥇</span>
                                                @elseif($user['rank'] == 2)
                                                    <span class="text-2xl">🥈</span>
                                                @elseif($user['rank'] == 3)
                                                    <span class="text-2xl">🥉</span>
                                                @endif
                                                <span class="ml-2 font-bold text-lg">{{ $user['rank'] }}</span>
                                            </div>
                                        @else
                                            <span class="font-bold">{{ $user['rank'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center space-x-3">
                                            <div class="avatar">
                                                <div class="mask mask-squircle w-12 h-12">
                                                    <div class="bg-primary text-primary-content rounded-lg flex items-center justify-center">
                                                        <span class="text-lg font-bold">{{ $user['initials'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold flex items-center gap-2">
                                                    {{ $user['name'] }}
                                                    @if($user['is_supporter'])
                                                        <span class="badge badge-warning badge-sm">⭐ Supporter</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm opacity-50">{{ $user['email'] }}</div>
                                                @if(!empty($user['badges']))
                                                    <div class="flex gap-1 mt-1">
                                                        @foreach(array_slice($user['badges'], 0, 3) as $badge)
                                                            <span class="badge badge-ghost badge-xs">{{ $badge }}</span>
                                                        @endforeach
                                                        @if(count($user['badges']) > 3)
                                                            <span class="badge badge-ghost badge-xs">+{{ count($user['badges']) - 3 }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-bold text-xl text-primary">{{ number_format($user['total_score']) }}</span>
                                    </td>
                                    <td>
                                        <span class="font-medium">{{ number_format($user['avg_score'], 1) }}</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <progress 
                                                class="progress progress-primary w-16" 
                                                value="{{ $user['avg_accuracy'] }}" 
                                                max="100"
                                            ></progress>
                                            <span class="text-sm">{{ number_format($user['avg_accuracy'], 1) }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline">{{ $user['predictions_count'] }}</span>
                                    </td>
                                    <td>
                                        @if($user['perfect_predictions'] > 0)
                                            <span class="badge badge-success">{{ $user['perfect_predictions'] }}</span>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="text-zinc-400 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-16 h-16 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400">No predictions found for the selected criteria.</p>
                    <p class="text-sm text-zinc-500">Try selecting a different season or type.</p>
                </div>
            @endif
        </div>
    </div>
</div>
