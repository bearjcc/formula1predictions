@extends('components.layouts.layout')

@section('content')
    <div class="mb-8">
        <h1 class="text-heading-1 mb-2">{{ __('Edit Prediction') }}</h1>
        <p class="text-zinc-600 dark:text-zinc-400">
            {{ __('Update your prediction for this race.') }}
        </p>
    </div>

    <livewire:predictions.prediction-form :existing-prediction="$prediction" />
@endsection
