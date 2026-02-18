@extends('components.layouts.layout')

@section('title', 'Global Leaderboard')
@section('headerSubtitle', 'Compete with other F1 prediction enthusiasts')

@section('content')
<div class="container mx-auto px-4 py-8">
    <livewire:global-leaderboard />
</div>
@endsection
