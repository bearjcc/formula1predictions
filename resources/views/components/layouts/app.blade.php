{{-- Livewire full-page components use this layout by default. Forwards to layout with title/headerSubtitle from @layoutData. --}}
<x-layouts.layout :title="$title ?? null" :headerSubtitle="$headerSubtitle ?? null">
    {{ $slot }}
</x-layouts.layout>
