<x-layouts.layout title="Admin – Feedback" headerSubtitle="View and moderate user feedback">
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
                            <th>From</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feedback as $f)
                            <tr>
                                <td class="font-medium">{{ $f->user?->name ?? '—' }}</td>
                                <td>{{ $f->subject ?? '—' }}</td>
                                <td class="max-w-xs truncate text-zinc-600 dark:text-zinc-400">{{ Str::limit($f->message, 80) }}</td>
                                <td class="text-zinc-600 dark:text-zinc-400">{{ $f->created_at->format('M j, Y H:i') }}</td>
                                <td>
                                    <form action="{{ route('admin.feedback.delete', $f) }}" method="POST" class="inline" onsubmit="return confirm('Delete this feedback?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-zinc-600 dark:text-zinc-400">No feedback yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($feedback->hasPages())
                <div class="mt-4">
                    {{ $feedback->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</x-layouts.layout>
