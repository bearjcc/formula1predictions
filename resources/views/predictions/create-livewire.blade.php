@extends('components.layouts.layout')

@section('title', __('Make Prediction'))
@section('headerSubtitle', __('Submit your predicted finishing order and fastest lap.'))

@section('content')
    <livewire:predictions.prediction-form :race="$race ?? null" />
@endsection
