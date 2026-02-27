{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ e(config('app.name')) }} News</title>
        <link>{{ url('/') }}</link>
        <description>News and announcements from {{ e(config('app.name')) }}</description>
        <language>{{ str_replace('_', '-', app()->getLocale()) }}</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ route('news.feed') }}" rel="self" type="application/rss+xml" />
        @foreach($items as $item)
        <item>
            <title>{{ e($item->title) }}</title>
            <link>{{ route('news.show', $item) }}</link>
            <description>{{ e($item->excerpt ?? Str::limit(strip_tags($item->body), 500)) }}</description>
            <pubDate>{{ $item->published_at->toRssString() }}</pubDate>
            <guid isPermaLink="true">{{ route('news.show', $item) }}</guid>
        </item>
        @endforeach
    </channel>
</rss>
