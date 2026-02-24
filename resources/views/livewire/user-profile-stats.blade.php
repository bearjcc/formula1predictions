<div class="space-y-6">
    <!-- User Info Card -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-20 h-20">
                            <span class="text-3xl font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
                        <p class="text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                        <p class="text-sm text-zinc-500">Member since {{ $user->created_at->format('M Y') }}</p>
                    </div>
                </div>
                
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Season</span>
                    </label>
                    <select wire:model.live="season" class="select select-bordered select-sm">
                        @foreach(range(date('Y'), 2022) as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Badges -->
            @if($user->is_season_supporter || !empty($user->getBadges()))
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <h4 class="text-sm font-semibold mb-2 text-zinc-700 dark:text-zinc-300">Badges</h4>
                    <div class="flex flex-wrap gap-2">
                        @if($user->is_season_supporter)
                            <span class="badge badge-warning gap-2">
                                ⭐ Season Supporter
                                <small class="opacity-75">{{ $user->supporter_since ? 'since ' . $user->supporter_since->format('Y') : '' }}</small>
                            </span>
                        @endif
                        @foreach($user->getBadges() as $badge)
                            @if($badge !== 'season-supporter')
                                <span class="badge badge-ghost">
                                    {{ ucfirst(str_replace('-', ' ', $badge)) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-primary">{{ $stats['total_predictions'] }}</div>
                <div class="text-sm text-zinc-600 dark:text-zinc-400">Predictions</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-secondary">{{ number_format($stats['total_score']) }}</div>
                <div class="text-sm text-zinc-600 dark:text-zinc-400">Total Points</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-accent">{{ number_format($stats['avg_score'], 1) }}</div>
                <div class="text-sm text-zinc-600 dark:text-zinc-400">Avg Score</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-info">{{ $stats['accuracy'] }}%</div>
                <div class="text-sm text-zinc-600 dark:text-zinc-400">Accuracy</div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-4">Performance Highlights</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Best Score</div>
                    <div class="stat-value text-primary text-2xl">{{ $stats['best_score'] }}</div>
                    <div class="stat-desc">Single prediction</div>
                </div>
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Perfect Predictions</div>
                    <div class="stat-value text-secondary text-2xl">{{ $stats['perfect_predictions'] }}</div>
                    <div class="stat-desc">Exact matches</div>
                </div>
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Races Participated</div>
                    <div class="stat-value text-accent text-2xl">{{ count($stats['race_performance']) }}</div>
                    <div class="stat-desc">In {{ $season }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Points Progression Chart -->
    @if(!empty($stats['points_over_time']))
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title mb-4">Points Progression</h3>
                <div style="height: 300px;">
                    <canvas id="points-chart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    @endif

    <!-- Accuracy Over Time Chart -->
    @if(!empty($stats['accuracy_over_time']))
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title mb-4">Accuracy Over Time</h3>
                <div style="height: 300px;">
                    <canvas id="accuracy-chart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    @endif

    <!-- Race Performance Table -->
    @if(!empty($stats['race_performance']))
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title mb-4">Race-by-Race Performance</h3>
                <div class="w-full max-w-full min-w-0 overflow-x-auto [-webkit-overflow-scrolling:touch]">
                    <table class="table table-zebra table-sm">
                        <thead>
                            <tr>
                                <th>Round</th>
                                <th>Score</th>
                                <th>Accuracy</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['race_performance'] as $race)
                                <tr>
                                    <td>Round {{ $race['race_round'] }}</td>
                                    <td>
                                        <span class="badge {{ $race['score'] >= 50 ? 'badge-success' : ($race['score'] >= 25 ? 'badge-primary' : 'badge-ghost') }}">
                                            {{ number_format($race['score'], 0) }} pts
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <progress 
                                                class="progress progress-xs w-20" 
                                                value="{{ $race['accuracy'] }}" 
                                                max="100"
                                            ></progress>
                                            <span class="text-xs">{{ number_format($race['accuracy'], 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Scripts for charts -->
    @if(!empty($pointsChartData) || !empty($accuracyChartData))
        <script>
            document.addEventListener('livewire:init', function () {
                // Points Chart
                @if(!empty($pointsChartData))
                    const pointsCtx = document.getElementById('points-chart');
                    if (pointsCtx) {
                        new Chart(pointsCtx, {
                            type: 'line',
                            data: @json($pointsChartData),
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    intersect: false,
                                    mode: 'index',
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': ' + context.parsed.y + ' pts';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Points'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Race Round'
                                        }
                                    }
                                }
                            }
                        });
                    }
                @endif

                // Accuracy Chart
                @if(!empty($accuracyChartData))
                    const accuracyCtx = document.getElementById('accuracy-chart');
                    if (accuracyCtx) {
                        new Chart(accuracyCtx, {
                            type: 'line',
                            data: @json($accuracyChartData),
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    intersect: false,
                                    mode: 'index',
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': ' + context.parsed.y + '%';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: 'Accuracy (%)'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Race Round'
                                        }
                                    }
                                }
                            }
                        });
                    }
                @endif
            });
        </script>
    @endif
</div>
