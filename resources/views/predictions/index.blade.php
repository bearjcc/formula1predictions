@extends('components.layouts.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Predictions</h1>
        <x-mary-button tag="a" href="{{ route('predict.create') }}" variant="primary" wire:navigate>Create New Prediction</x-mary-button>
    </div>

    @if($predictions->count() > 0)
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($predictions as $prediction)
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">{{ ucfirst($prediction->type) }} Prediction</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Season: {{ $prediction->season }}
                            @if($prediction->race_round)
                                | Round: {{ $prediction->race_round }}
                            @endif
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Status: <span class="badge badge-{{ $prediction->status === 'scored' ? 'success' : 'warning' }}">
                                {{ ucfirst($prediction->status) }}
                            </span>
                        </p>
                        @if($prediction->score > 0)
                            <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                                Score: {{ $prediction->score }}
                            </p>
                        @endif
                        <div class="card-actions justify-end">
                            <x-mary-button tag="a" href="{{ route('predictions.show', $prediction) }}" variant="outline" size="sm" wire:navigate>View</x-mary-button>
                            @if($prediction->status === 'draft')
                                <x-mary-button tag="a" href="{{ route('predictions.edit', $prediction) }}" variant="primary" size="sm" wire:navigate>Edit</x-mary-button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $predictions->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No predictions yet</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Start making predictions to see them here.</p>
            <x-mary-button tag="a" href="{{ route('predict.create') }}" variant="primary" wire:navigate>Create Your First Prediction</x-mary-button>
        </div>
    @endif
</div>
@endsection
