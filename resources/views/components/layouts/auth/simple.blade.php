<!DOCTYPE html>
@php $appearance = session('appearance', config('f1.default_appearance', 'system')); @endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => $appearance === 'dark']) data-appearance="{{ $appearance }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex h-12 w-12 mb-1 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-red-600 p-2">
                        <x-app-logo-icon class="size-8 fill-current text-white" />
                    </span>
                    <span class="text-xl font-bold text-zinc-900 dark:text-white">{{ config('app.name') }}</span>
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Predict race outcomes.</span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
