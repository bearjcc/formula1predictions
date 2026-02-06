@extends('components.layouts.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="{{ route('leaderboard.index') }}" class="btn btn-sm btn-outline mb-4">
            ← Back to Leaderboard
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">📊 Profile Statistics</h1>
    </div>

    <livewire:user-profile-stats :user="$user" />
</div>
@endsection
