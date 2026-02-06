@extends('components.layouts.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">🏆 Global Leaderboard</h1>
        <p class="text-zinc-600 dark:text-zinc-400 text-lg">Compete with other F1 prediction enthusiasts</p>
    </div>

    <livewire:global-leaderboard />
</div>
@endsection
