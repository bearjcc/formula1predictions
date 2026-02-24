@props(['teamName' => null])

@php
    $hex = \App\Models\Teams::constructorColor($teamName);
@endphp
<div class="flex items-stretch min-h-0">
    @if($hex)
        <div
            class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[1.25rem]"
            style="background-color: {{ $hex }}"
            aria-hidden="true"
        ></div>
        <div class="flex-1 min-w-0 pl-2">
            {{ $slot }}
        </div>
    @else
        <div class="flex-1 min-w-0">
            {{ $slot }}
        </div>
    @endif
</div>
