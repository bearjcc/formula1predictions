@extends('components.layouts.layout')

@section('content')
    @php
        /** @var \App\Models\Races|null $race */
    @endphp

    <livewire:predictions.prediction-form :race="$race ?? null" />
@endsection
