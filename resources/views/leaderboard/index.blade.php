<x-layouts.layout title="Leaderboard" headerSubtitle="F1 Predictions Rankings">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 flex flex-wrap items-center justify-end gap-4">
            <x-mary-button
                link="{{ route('leaderboard.compare', ['season' => $season]) }}"
                variant="outline"
                label="Head-to-Head Compare"
            />
        </div>

        <!-- Filters -->
        <x-mary-card class="mb-8 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Season</label>
                    <select
                        name="season"
                        class="select select-bordered w-full max-w-xs rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-red-600 dark:focus:ring-red-500"
                    >
                        @foreach ($seasons as $s)
                            <option value="{{ $s }}" {{ $season == $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
                    <select
                        name="type"
                        class="w-full max-w-xs rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-red-600 dark:focus:ring-red-500 px-3 py-2"
                    >
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}" {{ $type == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-mary-button type="submit" variant="primary" label="Filter" />
                </div>
            </form>
        </x-mary-card>

        <!-- Leaderboard Table -->
        <x-mary-card class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-heading-3 text-zinc-900 dark:text-zinc-100">
                    {{ $types[$type] }} - {{ $season }}
                </h3>
            </div>
            <div class="p-6">
                @if ($leaderboard->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Rank</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Avg Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Predictions</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($leaderboard as $user)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($user->rank <= 3)
                                                <div class="flex items-center">
                                                    @if ($user->rank == 1)
                                                        <span class="text-yellow-500 dark:text-yellow-400">🥇</span>
                                                    @elseif ($user->rank == 2)
                                                        <span class="text-zinc-400 dark:text-zinc-500">🥈</span>
                                                    @elseif ($user->rank == 3)
                                                        <span class="text-amber-600 dark:text-amber-500">🥉</span>
                                                    @endif
                                                    <span class="ml-2 font-bold text-zinc-900 dark:text-zinc-100">{{ $user->rank }}</span>
                                                </div>
                                            @else
                                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $user->rank }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <x-mary-avatar class="w-12 h-12 rounded-lg flex-shrink-0" placeholder="{{ strtoupper(substr($user->name, 0, 2)) }}" />
                                                <div>
                                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</div>
                                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user->predictions_count }} predictions</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-bold text-lg text-zinc-900 dark:text-zinc-100">{{ $user->total_score ?? 0 }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-700 dark:text-zinc-300">
                                            {{ number_format($user->avg_score ?? 0, 1) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-mary-badge variant="outline">{{ $user->predictions_count }}</x-mary-badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex gap-2">
                                                <x-mary-button
                                                    link="{{ route('leaderboard.user-stats', $user) }}"
                                                    variant="outline"
                                                    size="sm"
                                                    label="View Stats"
                                                />
                                                <x-mary-button
                                                    link="{{ route('leaderboard.compare', ['season' => $season, 'users' => $user->id]) }}"
                                                    variant="ghost"
                                                    size="sm"
                                                    label="Compare"
                                                />
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
        </x-mary-card>
    </div>
</x-layouts.layout>
