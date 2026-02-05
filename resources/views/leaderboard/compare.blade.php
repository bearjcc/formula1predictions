@extends('components.layouts.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Head-to-Head Comparison</h1>
                <p class="text-zinc-600 dark:text-zinc-400">Compare scores and accuracy with other predictors</p>
            </div>
            <a href="{{ route('leaderboard.index') }}" class="btn btn-outline">
                ← Back to Leaderboard
            </a>
        </div>
    </div>

    {{-- Filters: season + user multi-select --}}
    <div class="card bg-base-100 mb-8">
        <div class="card-body">
            <form method="GET" action="{{ route('leaderboard.compare') }}" class="flex flex-wrap gap-6 items-end">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Season</span>
                    </label>
                    <select name="season" class="select select-bordered">
                        @foreach($seasons as $s)
                            <option value="{{ $s }}" {{ $season == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control flex-1 min-w-[200px]">
                    <label class="label">
                        <span class="label-text">Select predictors to compare</span>
                    </label>
                    <select name="users[]" class="select select-bordered" multiple size="5">
                        @foreach($availableUsers as $u)
                            <option value="{{ $u->id }}" {{ in_array($u->id, $userIds) ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                    <label class="label">
                        <span class="label-text-alt">Hold Ctrl/Cmd to select multiple</span>
                    </label>
                </div>

                <div class="form-control">
                    <button type="submit" class="btn btn-primary">Compare</button>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($comparisonData))
        {{-- Summary table --}}
        <div class="card bg-base-100 mb-8">
            <div class="card-body">
                <h3 class="card-title mb-6">Comparison - {{ $season }} Season</h3>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Total Score</th>
                                <th>Avg Accuracy</th>
                                <th>Predictions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparisonData as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row['user'] }}</td>
                                    <td>{{ $row['total_score'] }}</td>
                                    <td>{{ $row['avg_accuracy'] }}%</td>
                                    <td>{{ $row['prediction_count'] }}</td>
                                    <td>
                                        <a href="{{ route('leaderboard.user-stats', $row['user_id']) }}" class="btn btn-sm btn-ghost">View stats</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Score progression chart --}}
        @if(!empty($progressionData))
            <div class="card bg-base-100 mb-8">
                <div class="card-body">
                    <h3 class="card-title mb-6">Cumulative Score Progression</h3>
                    <div class="relative" style="height: 300px;">
                        <canvas id="head-to-head-progression-chart"></canvas>
                    </div>
                </div>
            </div>

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
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title mb-2">Share this comparison</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">Copy the link to share with others:</p>
                <div class="flex gap-2 items-center">
                    <input type="text" readonly class="input input-bordered flex-1 font-mono text-sm" 
                           value="{{ url()->current() }}?season={{ $season }}&users={{ implode(',', $userIds) }}" 
                           id="share-url">
                    <button type="button" class="btn btn-outline btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('share-url').value); this.textContent='Copied!'">
                        Copy
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <p class="text-zinc-600 dark:text-zinc-400">Select one or more predictors above and click Compare to see head-to-head results.</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-500 mt-2">You can share the comparison URL with others once you have results.</p>
            </div>
        </div>
    @endif
</div>
@endsection
