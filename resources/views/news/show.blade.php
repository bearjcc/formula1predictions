<x-layouts.layout :title="$news->title" headerSubtitle="News">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="mb-6">
            <a href="{{ route('news.index') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-red-600 dark:hover:text-red-400" wire:navigate>&larr; Back to News</a>
        </div>
        <x-mary-card>
            <h1 class="text-heading-1 mb-4">{{ $news->title }}</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">
                {{ $news->published_at?->format('F j, Y \a\t g:i A') ?? $news->created_at->format('F j, Y \a\t g:i A') }}
                @if($news->user)
                    <span class="ml-2">by {{ $news->user->name }}</span>
                @endif
            </p>
            <div class="prose prose-zinc dark:prose-invert max-w-none text-auto-muted">
                {!! nl2br(e($news->body)) !!}
            </div>
        </x-mary-card>
    </div>
</x-layouts.layout>
