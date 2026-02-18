@extends('components.layouts.layout')

@section('content')
    @php
        /** @var \App\Models\Races|null $race */
    @endphp

    <div class="mb-8">
        <h1 class="text-heading-1 mb-2">{{ __('Make Prediction') }}</h1>
        <p class="text-zinc-600 dark:text-zinc-400">
            {{ __('Submit your predicted finishing order and fastest lap.') }}
        </p>
    </div>

    <livewire:predictions.prediction-form :race="$race ?? null" />
@endsection
