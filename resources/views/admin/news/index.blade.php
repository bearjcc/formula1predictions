<x-layouts.layout title="Admin – News" headerSubtitle="Manage news and announcements">
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">Back to Dashboard</a>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">New post</a>
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
                            <th>Title</th>
                            <th>Excerpt</th>
                            <th>Published</th>
                            <th>Author</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($news as $post)
                            <tr>
                                <td class="font-medium">{{ $post->title }}</td>
                                <td class="max-w-xs truncate text-zinc-600 dark:text-zinc-400">{{ Str::limit($post->excerpt ?? $post->body, 60) }}</td>
                                <td class="text-zinc-600 dark:text-zinc-400">
                                    @if($post->published_at)
                                        {{ $post->published_at->format('M j, Y') }}
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-500">Draft</span>
                                    @endif
                                </td>
                                <td class="text-zinc-600 dark:text-zinc-400">{{ $post->user?->name ?? '—' }}</td>
                                <td class="flex flex-wrap gap-2">
                                    @if($post->published_at)
                                        <a href="{{ route('news.show', $post) }}" class="btn btn-ghost btn-sm" target="_blank" rel="noopener">View</a>
                                    @endif
                                    <a href="{{ route('admin.news.edit', $post) }}" class="btn btn-outline btn-sm">Edit</a>
                                    <form action="{{ route('admin.news.destroy', $post) }}" method="POST" class="inline" onsubmit="return confirm('Delete this news post?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-zinc-600 dark:text-zinc-400">No news posts yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($news->hasPages())
                <div class="mt-4">
                    {{ $news->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</x-layouts.layout>
