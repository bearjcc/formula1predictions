<x-layouts.layout title="Admin – Users" headerSubtitle="View and manage registered users">
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Predictions</th>
                            <th>Total score</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td class="font-medium">{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->predictions_count ?? 0 }}</td>
                                <td>{{ $u->predictions_sum_score ?? 0 }}</td>
                                <td class="text-zinc-600 dark:text-zinc-400">{{ $u->created_at->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-zinc-600 dark:text-zinc-400">No users yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</x-layouts.layout>
