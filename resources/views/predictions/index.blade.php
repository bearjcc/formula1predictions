<x-layouts.layout title="My Predictions" headerSubtitle="Track your performance across the season.">
    <div class="mb-8 flex justify-end">
        <x-mary-button label="New Prediction" link="{{ route('races', ['year' => config('f1.current_season')]) }}" variant="primary" icon="o-plus" />
    </div>

    @if(session('success'))
        <div class="mb-6">
            <x-mary-alert title="Success" icon="o-check-circle" class="bg-green-50 text-green-800 border-green-200">
                {{ session('success') }}
            </x-mary-alert>
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-900/50 border-b border-zinc-200 dark:border-zinc-700">
                    <th class="p-4 font-bold text-sm text-zinc-500 uppercase tracking-wider">Race / Event</th>
                    <th class="p-4 font-bold text-sm text-zinc-500 uppercase tracking-wider">Status</th>
                    <th class="p-4 font-bold text-sm text-zinc-500 uppercase tracking-wider text-right">Score</th>
                    <th class="p-4 font-bold text-sm text-zinc-500 uppercase tracking-wider text-right">Accuracy</th>
                    <th class="p-4 font-bold text-sm text-zinc-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($predictions as $prediction)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-zinc-900 dark:text-zinc-100">
                                @if($prediction->type === 'race')
                                    {{ $prediction->race->race_name ?? "Round {$prediction->race_round}" }}
                                @else
                                    {{ ucfirst($prediction->type) }} Prediction
                                @endif
                            </div>
                            <div class="text-xs text-zinc-500">{{ $prediction->season }} Season</div>
                        </td>
                        <td class="p-4">
                            <x-mary-badge 
                                value="{{ ucfirst($prediction->status) }}" 
                                class="{{ $prediction->status === 'scored' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-zinc-100 text-zinc-800 border-zinc-200' }}"
                            />
                        </td>
                        <td class="p-4 text-right font-bold {{ $prediction->score > 0 ? 'text-green-600' : '' }}">
                            {{ $prediction->status === 'scored' ? number_format($prediction->score) : '-' }}
                        </td>
                        <td class="p-4 text-right">
                            {{ $prediction->status === 'scored' ? number_format($prediction->accuracy, 1) . '%' : '-' }}
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <x-mary-button icon="o-eye" link="{{ route('predictions.show', $prediction) }}" variant="ghost" size="sm" />
                                @if($prediction->isEditable())
                                    <x-mary-button icon="o-pencil" link="{{ route('predictions.edit', $prediction) }}" variant="ghost" size="sm" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center">
                            <div class="flex flex-col items-center">
                                <x-mary-icon name="o-no-symbol" class="w-12 h-12 text-zinc-300 mb-4" />
                                <p class="text-zinc-500 font-medium">You haven't made any predictions yet.</p>
                                <x-mary-button label="Check upcoming races" link="{{ route('races', ['year' => config('f1.current_season')]) }}" variant="outline" class="mt-4" />
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $predictions->links() }}
    </div>
</x-layouts.layout>
