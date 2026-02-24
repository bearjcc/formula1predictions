<x-layouts.layout title="My Predictions" headerSubtitle="Track your performance across the season.">
    <div class="mb-8 flex justify-end">
        <x-mary-button label="New Prediction" link="{{ route('races', ['year' => config('f1.current_season')]) }}" variant="primary" icon="o-plus" wire:navigate />
    </div>

    @if(session('success'))
        <div class="mb-6">
            <x-mary-alert title="Success" icon="o-check-circle" class="bg-green-50 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-200 dark:border-green-700">
                {{ session('success') }}
            </x-mary-alert>
        </div>
    @endif

    <div class="space-y-4">
        @forelse($predictions as $prediction)
            @php
                $url = $prediction->isEditable() ? route('predictions.edit', $prediction) : route('predictions.show', $prediction);
                $isNext = $nextRace && $prediction->race_id && (int) $prediction->race_id === (int) $nextRace->id;
                $isPast = $prediction->status === 'scored' || ($prediction->race && $prediction->race->isCompleted());
                $isFuture = $prediction->race && $prediction->race->isUpcoming() && !$isNext && !$isPast;
            @endphp
            <a href="{{ $url }}" wire:navigate class="block">
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 shadow-sm hover:border-red-300 dark:hover:border-red-700 hover:shadow-md transition-all cursor-pointer">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center flex-wrap gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                    @if($prediction->type === 'race' || $prediction->type === 'sprint')
                                        {{ $prediction->race->display_name ?? 'Round ' . $prediction->race_round }}
                                    @else
                                        {{ ucfirst($prediction->type) }} Prediction
                                    @endif
                                </h3>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $prediction->season }} Season</span>
                                @if($isNext)
                                    <x-mary-badge value="Next" class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border-blue-200" />
                                @elseif($isFuture)
                                    <x-mary-badge value="Future" class="bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200 border-zinc-200" />
                                @elseif($isPast)
                                    <x-mary-badge value="Past" class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-green-200" />
                                @endif
                                <x-mary-badge
                                    value="{{ ucfirst($prediction->status) }}"
                                    class="{{ $prediction->status === 'scored' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-green-200' : 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200 border-zinc-200' }}"
                                />
                            </div>
                            <div class="flex items-center gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                                @if($prediction->status === 'scored')
                                    <span class="font-semibold text-green-600 dark:text-green-400">{{ number_format($prediction->score) }} pts</span>
                                    <span>{{ number_format($prediction->accuracy, 1) }}% accuracy</span>
                                @else
                                    <span>{{ $prediction->isEditable() ? 'Edit' : 'View' }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0">
                            <x-mary-icon name="o-chevron-right" class="w-5 h-5 text-zinc-400 dark:text-zinc-500" />
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                <x-mary-icon name="o-no-symbol" class="w-12 h-12 text-zinc-300 dark:text-zinc-500 mb-4 mx-auto" />
                <p class="text-zinc-500 dark:text-zinc-400 font-medium">You haven't made any predictions yet.</p>
                <x-mary-button label="Check upcoming races" link="{{ route('races', ['year' => config('f1.current_season')]) }}" variant="outline" class="mt-4" wire:navigate />
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $predictions->links() }}
    </div>
</x-layouts.layout>
