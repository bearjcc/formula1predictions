<x-layouts.layout title="Admin – Races">
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-heading-1">Manage Races</h1>
            <p class="text-auto-muted">View race calendar and status</p>
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
                            <th>Race</th>
                            <th>Season</th>
                            <th>Round</th>
                            <th>Date</th>
                            <th>Circuit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($races as $race)
                            <tr>
                                <td class="font-medium">{{ $race->race_name }}</td>
                                <td>{{ $race->season }}</td>
                                <td>{{ $race->round }}</td>
                                <td class="text-zinc-600 dark:text-zinc-400">{{ $race->date ? \Carbon\Carbon::parse($race->date)->format('M j, Y') : '—' }}</td>
                                <td>{{ $race->circuit_name ?? $race->locality ?? '—' }}</td>
                                <td><span class="badge badge-outline">{{ ucfirst($race->status ?? 'scheduled') }}</span></td>
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
