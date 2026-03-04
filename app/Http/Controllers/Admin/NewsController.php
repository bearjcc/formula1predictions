<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    public function index(): View|RedirectResponse
    {
        $this->authorize('viewAny', News::class);

        try {
            $news = News::with('user')
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->paginate(20);

            return view('admin.news.index', compact('news'));
        } catch (\Throwable $e) {
            Log::error('Admin\NewsController@index failed', ['exception' => $e]);

            return redirect()->route('admin.dashboard')->with('error', 'Unable to load news. Please try again.');
        }
    }

    public function create(): View
    {
        $this->authorize('create', News::class);

        return view('admin.news.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', News::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);

        $slug = Str::slug($validated['title']);

        if (News::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.now()->format('YmdHis');
        }

        News::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'body' => $validated['body'],
            'excerpt' => $validated['excerpt'] ?? null,
            'published_at' => $validated['published_at'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.news.index')->with('success', 'News post created.');
    }

    public function edit(News $news): View
    {
        $this->authorize('update', $news);

        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $this->authorize('update', $news);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);

        $news->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'excerpt' => $validated['excerpt'] ?? null,
            'published_at' => $validated['published_at'] ?? null,
        ]);

        return redirect()->route('admin.news.index')->with('success', 'News post updated.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $this->authorize('delete', $news);

        $news->delete();

        return redirect()->back()->with('success', 'News post deleted.');
    }
}
