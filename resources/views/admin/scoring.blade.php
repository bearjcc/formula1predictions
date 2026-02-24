<x-layouts.layout title="Admin – Scoring" headerSubtitle="Score race predictions and manage results">
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex justify-end">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            @if(session('success'))
                <x-mary-alert icon="o-check-circle" class="alert-success mb-4" :title="session('success')" />
            @endif
            @if(session('error'))
                <x-mary-alert icon="o-x-circle" class="alert-error mb-4" :title="session('error')" />
            @endif

            <div class="w-full max-w-full min-w-0 overflow-x-auto [-webkit-overflow-scrolling:touch]">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Race</th>
                            <th>Season</th>
                            <th>Round</th>
                            <th>Date</th>
                            <th>Pending predictions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($races as $race)
                            <tr>
                                <td class="font-medium">{{ $race->display_name }}</td>
                                <td>{{ $race->season }}</td>
                                <td>{{ $race->round }}</td>
                                <td class="text-zinc-600 dark:text-zinc-400">{{ $race->date ? \Carbon\Carbon::parse($race->date)->format('M j, Y') : '—' }}</td>
                                <td>{{ $race->predictions_count ?? 0 }}</td>
                                <td class="flex flex-wrap gap-2">
                                    @can('manageResults', $race)
                                        <form action="{{ route('admin.races.score', $race) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">Score now</button>
                                        </form>
                                        <form action="{{ route('admin.races.queue-scoring', $race) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="force_update" value="0">
                                            <button type="submit" class="btn btn-outline btn-sm">Queue scoring</button>
                                        </form>
                                        <form action="{{ route('admin.races.toggle-half-points', $race) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm">{{ $race->half_points ? 'Disable' : 'Enable' }} half-points</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-zinc-600 dark:text-zinc-400">No races yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($races->hasPages())
                <div class="mt-4">
                    {{ $races->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</x-layouts.layout>
