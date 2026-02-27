<?php

declare(strict_types=1);

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public news index returns 200 and empty state when no news', function () {
    $this->get(route('news.index'))
        ->assertOk()
        ->assertSee('No news posts yet');
});

test('public news index includes RSS feed discovery link in head', function () {
    $response = $this->get(route('news.index'))
        ->assertOk();

    $html = $response->getContent();
    expect($html)
        ->toContain('rel="alternate"')
        ->toContain('type="application/rss+xml"')
        ->toContain('href="'.url('/news/feed').'"');
});

test('public news index shows published posts', function () {
    News::create([
        'title' => 'First post',
        'slug' => 'first-post',
        'body' => 'Content here.',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('news.index'))
        ->assertOk()
        ->assertSee('First post')
        ->assertSee('Content here.');
});

test('public news show returns 200 for published post', function () {
    $post = News::create([
        'title' => 'Single post',
        'slug' => 'single-post',
        'body' => 'Full body content.',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('news.show', $post))
        ->assertOk()
        ->assertSee('Single post')
        ->assertSee('Full body content.');
});

test('public news show returns 404 for draft post', function () {
    $post = News::create([
        'title' => 'Draft post',
        'slug' => 'draft-post',
        'body' => 'Draft body.',
        'published_at' => null,
    ]);

    $this->get(route('news.show', $post))
        ->assertNotFound();
});

test('public news show returns 404 for future published post', function () {
    $post = News::create([
        'title' => 'Future post',
        'slug' => 'future-post',
        'body' => 'Future body.',
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('news.show', $post))
        ->assertNotFound();
});

test('news feed returns 200 and valid RSS structure', function () {
    $response = $this->get(route('news.feed'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=utf-8');

    $body = $response->getContent();
    expect($body)
        ->toContain('<rss')
        ->toContain('<channel')
        ->toContain('<title>')
        ->toContain('</channel>')
        ->toContain('</rss>');
});

test('news feed includes published items with title link and pubDate', function () {
    $post = News::create([
        'title' => 'RSS post',
        'slug' => 'rss-post',
        'body' => 'RSS body.',
        'excerpt' => 'RSS excerpt',
        'published_at' => now()->subHour(),
    ]);

    $response = $this->get(route('news.feed'))
        ->assertOk();

    $body = $response->getContent();
    expect($body)
        ->toContain('<item>')
        ->toContain('<title>RSS post</title>')
        ->toContain(route('news.show', $post))
        ->toContain('<pubDate>')
        ->toContain('RSS excerpt');
});

test('news feed route does not match show route', function () {
    $this->get(route('news.feed'))
        ->assertOk();
    $this->get('/news/feed')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=utf-8');
});
