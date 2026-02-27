<x-layouts.layout title="News" headerSubtitle="Updates and announcements">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-heading-1">News</h1>
            <a href="{{ route('news.feed') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-red-600 dark:hover:text-red-400 flex items-center gap-1" aria-label="RSS feed">
                <x-mary-icon name="o-signal" class="w-5 h-5" />
                <span>RSS feed</span>
            </a>
        </div>

        @forelse($news as $post)
            <x-mary-card class="mb-6">
                <div class="flex flex-col gap-2">
                    <h2 class="text-heading-3">
                        <a href="{{ route('news.show', $post) }}" class="text-zinc-900 dark:text-zinc-100 hover:text-red-600 dark:hover:text-red-400" wire:navigate>
                            {{ $post->title }}
                        </a>
                    </h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}
                    </p>
                    @if($post->excerpt)
                        <p class="text-auto-muted">{{ $post->excerpt }}</p>
                    @else
                        <p class="text-auto-muted">{{ Str::limit(strip_tags($post->body), 200) }}</p>
                    @endif
                    <a href="{{ route('news.show', $post) }}" class="btn btn-ghost btn-sm self-start" wire:navigate>Read more</a>
                </div>
            </x-mary-card>
        @empty
            <x-mary-card>
                <p class="text-auto-muted">No news posts yet. Check back later.</p>
            </x-mary-card>
        @endforelse

        @if($news->hasPages())
            <div class="mt-6">
                {{ $news->links() }}
            </div>
        @endif
    </div>
</x-layouts.layout>
