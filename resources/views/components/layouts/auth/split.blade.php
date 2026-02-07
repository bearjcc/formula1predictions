<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-appearance="{{ session('appearance', config('f1.default_appearance', 'system')) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-gradient-to-br from-red-900/30 via-neutral-900 to-neutral-900"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-2 text-lg font-bold" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-600 p-2">
                        <x-app-logo-icon class="h-6 fill-current text-white" />
                    </span>
                    {{ config('app.name', 'F1 Predictions') }}
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <h2 class="text-xl font-semibold">&ldquo;{{ trim($message) }}&rdquo;</h2>
                        <footer><h3 class="font-semibold">{{ trim($author) }}</h3></footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-red-600 p-2">
                            <x-app-logo-icon class="size-8 fill-current text-white" />
                        </span>
                        <span class="text-xl font-bold text-zinc-900 dark:text-white">{{ config('app.name', 'F1 Predictions') }}</span>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Predict race outcomes. Compete with friends.</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
