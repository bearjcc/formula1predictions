<x-layouts.layout :title="'Admin – '.$race->display_name" headerSubtitle="Admin-only race operations">
<div class="container mx-auto px-4 py-8 space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-semibold">{{ $race->display_name }}</h2>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Season {{ $race->season }} · Round {{ $race->round }} · {{ $race->circuit_name ?? $race->locality ?? 'Circuit TBD' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.scoring') }}" class="btn btn-outline">Back to Scoring</a>
            <a href="{{ route('admin.races') }}" class="btn btn-ghost">All Races</a>
        </div>
    </div>

    @if(session('success'))
        <x-mary-alert icon="o-check-circle" class="alert-success" :title="session('success')" />
    @endif
    @if(session('error'))
        <x-mary-alert icon="o-x-circle" class="alert-error" :title="session('error')" />
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="card bg-base-100">
            <div class="card-body">
                <p class="text-sm text-zinc-500">Status</p>
                <p class="text-lg font-semibold">{{ ucfirst($race->status ?? 'scheduled') }}</p>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body">
                <p class="text-sm text-zinc-500">Stored results</p>
                <p class="text-lg font-semibold">{{ count($resultRows) }}</p>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body">
                <p class="text-sm text-zinc-500">Active race predictions</p>
                <p class="text-lg font-semibold">{{ $race->active_race_predictions_count }}</p>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body">
                <p class="text-sm text-zinc-500">Active sprint predictions</p>
                <p class="text-lg font-semibold">{{ $race->active_sprint_predictions_count }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-8">
            <div class="card bg-base-100">
                <div class="card-body space-y-4">
                    <div>
                        <h3 class="card-title">Race Operations</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Fetch official results, score predictions, or change race state.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <form action="{{ route('admin.races.fetch-results', $race) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-full">Fetch race results</button>
                        </form>

                        <form action="{{ route('admin.races.score', $race) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-full">Score now</button>
                        </form>

                        <form action="{{ route('admin.races.queue-scoring', $race) }}" method="POST">
                            @csrf
                            <input type="hidden" name="force_update" value="1">
                            <button type="submit" class="btn btn-outline w-full">Queue scoring with refresh</button>
                        </form>

                        <form action="{{ route('admin.races.toggle-half-points', $race) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline w-full">
                                {{ $race->half_points ? 'Disable' : 'Enable' }} half-points
                            </button>
                        </form>
                    </div>

                    <form action="{{ route('admin.races.cancel', $race) }}" method="POST" class="space-y-3">
                        @csrf
                        <label class="form-control w-full">
                            <span class="label-text font-medium">Cancellation reason</span>
                            <textarea name="reason" rows="3" class="textarea textarea-bordered w-full" placeholder="Optional reason shown in admin notes">{{ old('reason') }}</textarea>
                        </label>
                        @error('reason')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="btn btn-error" onclick="return confirm('Cancel this race and mark active predictions as cancelled?');">Cancel race</button>
                    </form>
                </div>
            </div>

            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="card-title">Race Predictions</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Latest 25 linked race predictions, with inline score overrides.</p>
                        </div>
                        <span class="badge badge-outline">{{ $race->scored_race_predictions_count }} scored</span>
                    </div>

                    <div class="w-full overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Updated</th>
                                    <th>Override</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($racePredictions as $prediction)
                                    <tr>
                                        <td class="font-medium">{{ $prediction->user?->name ?? '—' }}</td>
                                        <td><span class="badge badge-outline">{{ ucfirst($prediction->status) }}</span></td>
                                        <td>{{ $prediction->score ?? '—' }}</td>
                                        <td class="text-sm text-zinc-600 dark:text-zinc-400">{{ $prediction->updated_at?->format('M j, Y H:i') ?? '—' }}</td>
                                        <td>
                                            <form action="{{ route('admin.predictions.override-score', $prediction) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                                @csrf
                                                <input type="number" name="score" value="{{ old('score', $prediction->score ?? 0) }}" class="input input-bordered input-sm w-24" min="-100" max="500">
                                                <input type="text" name="reason" value="{{ old('reason') }}" class="input input-bordered input-sm w-48" placeholder="Reason">
                                                <button type="submit" class="btn btn-outline btn-sm">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-zinc-600 dark:text-zinc-400">No race predictions linked to this race.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($race->hasSprint())
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="card-title">Sprint Predictions</h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Sprint-linked predictions for the same weekend.</p>
                            </div>
                            <span class="badge badge-outline">{{ $race->scored_sprint_predictions_count }} scored</span>
                        </div>

                        <div class="w-full overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Score</th>
                                        <th>Updated</th>
                                        <th>Override</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sprintPredictions as $prediction)
                                        <tr>
                                            <td class="font-medium">{{ $prediction->user?->name ?? '—' }}</td>
                                            <td><span class="badge badge-outline">{{ ucfirst($prediction->status) }}</span></td>
                                            <td>{{ $prediction->score ?? '—' }}</td>
                                            <td class="text-sm text-zinc-600 dark:text-zinc-400">{{ $prediction->updated_at?->format('M j, Y H:i') ?? '—' }}</td>
                                            <td>
                                                <form action="{{ route('admin.predictions.override-score', $prediction) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                                    @csrf
                                                    <input type="number" name="score" value="{{ old('score', $prediction->score ?? 0) }}" class="input input-bordered input-sm w-24" min="-100" max="500">
                                                    <input type="text" name="reason" value="{{ old('reason') }}" class="input input-bordered input-sm w-48" placeholder="Reason">
                                                    <button type="submit" class="btn btn-outline btn-sm">Save</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-zinc-600 dark:text-zinc-400">No sprint predictions linked to this race.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-8">
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="card-title">Race Snapshot</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500">Race date</dt>
                            <dd>{{ $race->date ? \Carbon\Carbon::parse($race->date)->format('M j, Y') : 'TBD' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500">Qualifying</dt>
                            <dd>{{ $race->qualifying_start?->format('M j, Y H:i') ?? 'TBD' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500">Sprint qualifying</dt>
                            <dd>{{ $race->sprint_qualifying_start?->format('M j, Y H:i') ?? 'Not scheduled' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500">Half-points</dt>
                            <dd>{{ $race->half_points ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="card-title">Stored Results</h3>
                    @if($resultRows !== [])
                        <div class="space-y-3">
                            @foreach(array_slice($resultRows, 0, 10) as $index => $result)
                                <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-800">
                                    <span class="font-semibold">P{{ $index + 1 }}</span>
                                    <span>{{ data_get($result, 'driver.name') ?? data_get($result, 'driverId') ?? data_get($result, 'driver.driverId') ?? 'Unknown driver' }}</span>
                                    <span class="text-zinc-500">{{ data_get($result, 'status', '—') }}</span>
                                </div>
                            @endforeach
                            @if(count($resultRows) > 10)
                                <p class="text-sm text-zinc-500">Showing first 10 results of {{ count($resultRows) }} stored rows.</p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">No race results are currently stored for this race.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.layout>
