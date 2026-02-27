<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends Controller
{
    /**
     * Public news index (paginated, newest first).
     */
    public function index(): View
    {
        $news = News::published()
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('news.index', compact('news'));
    }

    /**
     * Show a single news post (by id; public).
     */
    public function show(News $news): View|Response
    {
        if (! $news->published_at || $news->published_at->isFuture()) {
            abort(404);
        }

        return view('news.show', compact('news'));
    }

    /**
     * RSS 2.0 feed (public).
     */
    public function feed(): Response
    {
        $items = News::published()
            ->orderByDesc('published_at')
            ->limit(50)
            ->get();

        $xml = view('news.feed', ['items' => $items])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8',
        ]);
    }
}
