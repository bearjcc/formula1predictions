<div class="space-y-6">
    <x-mary-card class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="space-y-4">
            <div>
                <h2 class="text-heading-3 text-zinc-900 dark:text-zinc-100">Prediction Leaderboard</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Filter the season table, then jump into detailed stats or a head-to-head comparison.
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-4">
                <div class="min-w-[140px] space-y-1">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Season</label>
                    <select wire:model.live="season" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 focus:ring-2 focus:ring-red-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-red-500">
                        @foreach ($availableSeasons as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[180px] space-y-1">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
                    <select wire:model.live="type" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 focus:ring-2 focus:ring-red-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-red-500">
                        <option value="all">All Predictions</option>
                        <option value="race">Race Predictions</option>
                        <option value="preseason">Preseason Predictions</option>
                    </select>
                </div>

                <div class="min-w-[180px] space-y-1">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort By</label>
                    <select wire:model.live="sortBy" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 focus:ring-2 focus:ring-red-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-red-500">
                        <option value="total_score">Total Score</option>
                        <option value="avg_score">Average Score</option>
                        <option value="predictions_count">Predictions</option>
                    </select>
                </div>
            </div>
        </div>
    </x-mary-card>

    @if (! empty($proStats))
        <div class="grid gap-4 md:grid-cols-4">
            <x-mary-card class="border border-zinc-200 dark:border-zinc-700">
                <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Active Users</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $proStats['total_users'] }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Scored predictors in {{ $season }}</p>
            </x-mary-card>

            <x-mary-card class="border border-zinc-200 dark:border-zinc-700">
                <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Predictions</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $proStats['total_predictions'] }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Included in this leaderboard</p>
            </x-mary-card>

            <x-mary-card class="border border-zinc-200 dark:border-zinc-700">
                <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Average Total Score</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $proStats['avg_total_score'] }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Median: {{ $proStats['median_score'] }}</p>
            </x-mary-card>

            <x-mary-card class="border border-zinc-200 dark:border-zinc-700">
                <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Perfect Predictions</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $proStats['perfect_predictions'] }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Scores meeting the perfect threshold</p>
            </x-mary-card>
        </div>
    @endif

    <x-mary-card class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        @if ($paginatedLeaderboard->count() > 0)
            @php
                $currentUserId = auth()->id();
            @endphp
            <div class="w-full max-w-full min-w-0 overflow-x-auto [-webkit-overflow-scrolling:touch]">
                <table class="w-full">
                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Avg Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Predictions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Perfect</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                        @foreach ($paginatedLeaderboard as $user)
                            <tr class="{{ $currentUserId && (int) $user['id'] === (int) $currentUserId ? 'border-l-4 border-red-500 bg-red-50/40 dark:border-red-400 dark:bg-red-900/20' : 'hover:bg-zinc-50 dark:hover:bg-zinc-700/40' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-zinc-100">
                                        @if ($user['rank'] === 1)
                                            <span aria-hidden="true">🥇</span>
                                        @elseif ($user['rank'] === 2)
                                            <span aria-hidden="true">🥈</span>
                                        @elseif ($user['rank'] === 3)
                                            <span aria-hidden="true">🥉</span>
                                        @endif
                                        <span>{{ $user['rank'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <x-mary-avatar class="h-12 w-12 flex-shrink-0 rounded-lg" placeholder="{{ $user['initials'] }}" />
                                        <div>
                                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $user['name'] }}</div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user['predictions_count'] }} predictions</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($user['total_score']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-zinc-700 dark:text-zinc-300">{{ number_format($user['avg_score'], 1) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-mary-badge variant="outline">{{ $user['predictions_count'] }}</x-mary-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user['perfect_predictions'] > 0)
                                        <span class="inline-flex items-center rounded-full bg-green-600 px-2.5 py-1 text-xs font-medium text-white">
                                            {{ $user['perfect_predictions'] }}
                                        </span>
                                    @else
                                        <span class="text-zinc-600 dark:text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-2">
                                        <x-mary-button link="{{ route('leaderboard.user-stats', $user['id']) }}" variant="outline" size="sm" label="View Stats" />
                                        <x-mary-button link="{{ route('leaderboard.compare', ['season' => $season, 'users' => $user['id']]) }}" variant="ghost" size="sm" label="Compare" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                {{ $paginatedLeaderboard->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-lg text-zinc-600 dark:text-zinc-400">No predictions found for the selected criteria.</p>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Try selecting a different season or type.</p>
            </div>
        @endif
    </x-mary-card>
</div>
