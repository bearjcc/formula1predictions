@extends('components.layouts.layout')

@section('content')
    <livewire:predictions.prediction-form :existing-prediction="$prediction" />
@endsection
