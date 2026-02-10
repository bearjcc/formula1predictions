<x-layouts.layout title="Admin – Predictions">
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-heading-1">Manage Predictions</h1>
            <p class="text-auto-muted">Score, lock, unlock, or delete predictions</p>
        </div>
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

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Type</th>
                            <th>Season</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($predictions as $p)
                            <tr>
                                <td class="font-medium">{{ $p->user?->name ?? '—' }}</td>
                                <td>{{ ucfirst($p->type) }}</td>
                                <td>{{ $p->season }}</td>
                                <td><span class="badge badge-outline">{{ ucfirst($p->status) }}</span></td>
                                <td>{{ $p->score !== null ? (int) $p->score : '—' }}</td>
                                <td class="text-zinc-600 dark:text-zinc-400">{{ $p->updated_at->format('M j, Y H:i') }}</td>
                                <td class="flex flex-wrap gap-2">
                                    @if($p->type === 'race' && $p->race_id && in_array($p->status, ['submitted', 'locked']))
                                        <form action="{{ route('admin.predictions.score', $p) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">Score</button>
                                        </form>
                                    @endif
                                    @if(in_array($p->status, ['draft', 'submitted']))
                                        <form action="{{ route('admin.predictions.lock', $p) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">Lock</button>
                                        </form>
                                    @endif
                                    @if($p->status === 'locked')
                                        <form action="{{ route('admin.predictions.unlock', $p) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">Unlock</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.predictions.delete', $p) }}" method="POST" class="inline" onsubmit="return confirm('Delete this prediction?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-zinc-600 dark:text-zinc-400">No predictions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($predictions->hasPages())
                <div class="mt-4">
                    {{ $predictions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</x-layouts.layout>
