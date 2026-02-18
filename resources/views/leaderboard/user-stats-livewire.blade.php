@extends('components.layouts.layout')

@section('title', $user->name . "'s Statistics")
@section('headerSubtitle', 'Profile Statistics')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex justify-end">
        <a href="{{ route('leaderboard.index') }}" class="btn btn-sm btn-outline">
            Back to Leaderboard
        </a>
    </div>

    <livewire:user-profile-stats :user="$user" />
</div>
@endsection
