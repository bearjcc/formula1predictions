<x-layouts.layout title="Head-to-Head Comparison" headerSubtitle="Compare scores and accuracy with other predictors">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 flex justify-end">
            <x-mary-button link="{{ route('leaderboard.index') }}" variant="outline" label="Back to Leaderboard" />
        </div>

        {{-- Filters: season + user multi-select --}}
        <x-mary-card class="mb-8 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
            <form method="GET" action="{{ route('leaderboard.compare') }}" class="flex flex-wrap gap-6 items-end">
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Season</label>
                    <select
                        name="season"
                        class="w-full min-w-[120px] rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-red-600 dark:focus:ring-red-500 px-3 py-2"
                    >
                        @foreach ($seasons as $s)
                            <option value="{{ $s }}" {{ $season == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[200px] space-y-1">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Select predictors to compare</label>
                    <select
                        name="users[]"
                        multiple
                        size="5"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-red-600 dark:focus:ring-red-500 px-3 py-2"
                    >
                        @foreach ($availableUsers as $u)
                            <option value="{{ $u->id }}" {{ in_array($u->id, $userIds) ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Hold Ctrl/Cmd to select multiple</p>
                </div>

                <div>
                    <x-mary-button type="submit" variant="primary" label="Compare" />
                </div>
            </form>
        </x-mary-card>

        @if (! empty($comparisonData))
            {{-- Summary table --}}
            <x-mary-card class="mb-8 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-heading-3 text-zinc-900 dark:text-zinc-100">Comparison - {{ $season }} Season</h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Avg Accuracy</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Predictions</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($comparisonData as $row)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-zinc-900 dark:text-zinc-100">{{ $row['user'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-700 dark:text-zinc-300">{{ $row['total_score'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-700 dark:text-zinc-300">{{ $row['avg_accuracy'] }}%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-700 dark:text-zinc-300">{{ $row['prediction_count'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-mary-button
                                                link="{{ route('leaderboard.user-stats', $row['user_id']) }}"
                                                variant="ghost"
                                                size="sm"
                                                label="View stats"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-mary-card>

            {{-- Score progression chart --}}
            @if (! empty($progressionData))
                <x-mary-card class="mb-8 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-heading-3 text-zinc-900 dark:text-zinc-100">Cumulative Score Progression</h3>
                    </div>
                    <div class="p-6">
                        <div class="relative" style="height: 300px;">
                            <canvas id="head-to-head-progression-chart"></canvas>
                        </div>
                    </div>
                </x-mary-card>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const progressionData = @json($progressionData);
                        if (progressionData.length === 0) return;

                        const ctx = document.getElementById('head-to-head-progression-chart');
                        if (!ctx) return;

                        const raceLabels = progressionData.map(r => r.race);
                        const userNames = Object.keys(progressionData[0]).filter(k => !['race', 'round', 'date'].includes(k));

                        const colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4'];
                        const datasets = userNames.map((name, i) => ({
                            label: name,
                            data: progressionData.map(r => r[name] ?? 0),
                            borderColor: colors[i % colors.length],
                            backgroundColor: colors[i % colors.length] + '20',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.1,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                        }));

                        new Chart(ctx, {
                            type: 'line',
                            data: { labels: raceLabels, datasets },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { intersect: false, mode: 'index' },
                                scales: {
                                    y: { beginAtZero: true, title: { display: true, text: 'Cumulative Score' } },
                                    x: { title: { display: true, text: 'Race' } },
                                },
                                plugins: {
                                    legend: { position: 'top' },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => ctx.dataset.label + ': ' + ctx.parsed.y + ' pts',
                                        },
                                    },
                                },
                            },
                        });
                    });
                </script>
            @endif

            {{-- Shareable link --}}
            <x-mary-card class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-heading-3 text-zinc-900 dark:text-zinc-100">Share this comparison</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">Copy the link to share with others:</p>
                    <div class="flex gap-2 items-center">
                        <input
                            type="text"
                            readonly
                            id="share-url"
                            value="{{ url()->current() }}?season={{ $season }}&users={{ implode(',', $userIds) }}"
                            class="flex-1 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-mono text-sm px-3 py-2"
                        />
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('share-url').value); this.textContent='Copied!';"
                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:ring-2 focus:ring-red-600 dark:focus:ring-red-500"
                        >
                            Copy
                        </button>
                    </div>
                </div>
            </x-mary-card>
        @else
            <x-mary-card class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <div class="p-6 text-center py-12">
                    <p class="text-zinc-600 dark:text-zinc-400">Select one or more predictors above and click Compare to see head-to-head results.</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-500 mt-2">You can share the comparison URL with others once you have results.</p>
                </div>
            </x-mary-card>
        @endif
    </div>
</x-layouts.layout>
