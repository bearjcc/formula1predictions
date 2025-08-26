@extends('components.layouts.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ ucfirst($prediction->type) }} Prediction
            </h1>
            <div class="flex gap-2">
                @if($prediction->status === 'draft')
                    <a href="{{ route('predictions.edit', $prediction) }}" class="btn btn-primary">Edit</a>
                @endif
                <a href="{{ route('predictions.index') }}" class="btn btn-outline">Back to List</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Prediction Details</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-300">Type:</dt>
                                <dd class="text-gray-900 dark:text-white">{{ ucfirst($prediction->type) }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-300">Season:</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $prediction->season }}</dd>
                            </div>
                            @if($prediction->race_round)
                                <div>
                                    <dt class="font-medium text-gray-700 dark:text-gray-300">Race Round:</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $prediction->race_round }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-300">Status:</dt>
                                <dd>
                                    <span class="badge badge-{{ $prediction->status === 'scored' ? 'success' : 'warning' }}">
                                        {{ ucfirst($prediction->status) }}
                                    </span>
                                </dd>
                            </div>
                            @if($prediction->score > 0)
                                <div>
                                    <dt class="font-medium text-gray-700 dark:text-gray-300">Score:</dt>
                                    <dd class="text-green-600 dark:text-green-400 font-semibold">{{ $prediction->score }}</dd>
                                </div>
                            @endif
                            @if($prediction->accuracy)
                                <div>
                                    <dt class="font-medium text-gray-700 dark:text-gray-300">Accuracy:</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ number_format($prediction->accuracy, 1) }}%</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Timeline</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-300">Created:</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $prediction->created_at->format('M j, Y g:i A') }}</dd>
                            </div>
                            @if($prediction->submitted_at)
                                <div>
                                    <dt class="font-medium text-gray-700 dark:text-gray-300">Submitted:</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $prediction->submitted_at->format('M j, Y g:i A') }}</dd>
                                </div>
                            @endif
                            @if($prediction->locked_at)
                                <div>
                                    <dt class="font-medium text-gray-700 dark:text-gray-300">Locked:</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $prediction->locked_at->format('M j, Y g:i A') }}</dd>
                                </div>
                            @endif
                            @if($prediction->scored_at)
                                <div>
                                    <dt class="font-medium text-gray-700 dark:text-gray-300">Scored:</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $prediction->scored_at->format('M j, Y g:i A') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                @if($prediction->notes)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2">Notes</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ $prediction->notes }}</p>
                    </div>
                @endif

                @if(!empty($prediction->prediction_data))
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">Prediction Data</h3>
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                            <pre class="text-sm text-gray-700 dark:text-gray-300 overflow-x-auto">{{ json_encode($prediction->prediction_data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
