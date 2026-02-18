@extends('components.layouts.layout')

@section('title', __('Edit Prediction'))
@section('headerSubtitle', __('Update your prediction for this race.'))

@section('content')
    <livewire:predictions.prediction-form :existing-prediction="$prediction" />
@endsection
