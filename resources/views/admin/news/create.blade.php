<x-layouts.layout title="Admin – New post" headerSubtitle="Create a news or announcement">
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('admin.news.index') }}" class="btn btn-outline">Back to News</a>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            @if(session('error'))
                <x-mary-alert icon="o-x-circle" class="alert-error mb-4" :title="session('error')" />
            @endif

            <form action="{{ route('admin.news.store') }}" method="POST">
                @csrf
                <div class="form-control w-full mb-4">
                    <label class="label" for="title">
                        <span class="label-text">Title</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                        class="input input-bordered w-full @error('title') input-error @enderror"
                        placeholder="Post title" />
                    @error('title')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-control w-full mb-4">
                    <label class="label" for="excerpt">
                        <span class="label-text">Excerpt (optional)</span>
                    </label>
                    <textarea id="excerpt" name="excerpt" rows="2" maxlength="500"
                        class="textarea textarea-bordered w-full @error('excerpt') textarea-error @enderror"
                        placeholder="Short summary for listings and RSS">{{ old('excerpt') }}</textarea>
                    @error('excerpt')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-control w-full mb-4">
                    <label class="label" for="body">
                        <span class="label-text">Body</span>
                    </label>
                    <textarea id="body" name="body" rows="12" required
                        class="textarea textarea-bordered w-full @error('body') textarea-error @enderror"
                        placeholder="Full content (plain text or HTML)">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-control w-full mb-6">
                    <label class="label" for="published_at">
                        <span class="label-text">Publish at (optional, leave empty for draft)</span>
                    </label>
                    <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at') }}"
                        class="input input-bordered w-full @error('published_at') input-error @enderror" />
                    @error('published_at')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">Create post</button>
                    <a href="{{ route('admin.news.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</x-layouts.layout>
