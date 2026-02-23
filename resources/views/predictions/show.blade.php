<x-layouts.layout :title="($prediction->type === 'race' ? ($prediction->race->display_name ?? 'Round ' . $prediction->race_round) : ucfirst($prediction->type)) . ' Prediction'" headerSubtitle="Performance overview and details.">
    <div class="mb-8 flex justify-end">
        <div class="flex items-center space-x-2">
            @if($prediction->isEditable())
                <x-mary-button label="Edit" link="{{ route('predictions.edit', $prediction) }}" variant="primary" icon="o-pencil" />
            @endif
            <x-mary-button label="Back to List" link="{{ route('predictions.index') }}" variant="ghost" />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Prediction View -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                    <h3 class="font-bold">Predicted Finishing Order</h3>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @php
                        $order = $prediction->getPredictedDriverOrder();
                        $fastestLap = $prediction->getPredictedFastestLap();
                    @endphp
                    
                    @foreach($order as $index => $driverId)
                        @php
                            $driver = \App\Models\Drivers::where('driverId', $driverId)->first();
                        @endphp
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <span class="w-8 h-8 rounded bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center font-bold text-sm">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $driver ? "{$driver->name} {$driver->surname}" : $driverId }}
                                    </div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ $driver->team->display_name ?? 'Individual' }}
                                    </div>
                                </div>
                            </div>
                            
                            @if($fastestLap === $driverId)
                                <x-mary-badge value="Fastest Lap" class="bg-red-600 text-white border-none text-[10px]" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar Stats -->
        <div class="space-y-6">
            <div class="bg-zinc-900 text-white rounded-lg p-6 shadow-xl border border-zinc-800">
                <h3 class="text-lg font-bold mb-4 uppercase tracking-widest text-zinc-300 dark:text-zinc-200">Summary</h3>
                <div class="space-y-6">
                    <div>
                        <p class="text-xs font-bold text-zinc-300 dark:text-zinc-200 uppercase tracking-widest mb-1">Status</p>
                        <p class="text-xl font-bold">{{ ucfirst($prediction->status) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-300 dark:text-zinc-200 uppercase tracking-widest mb-1">Score</p>
                        <p class="text-3xl font-black text-green-400 dark:text-green-300">{{ $prediction->status === 'scored' ? number_format($prediction->score) : 'Pending' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-300 dark:text-zinc-200 uppercase tracking-widest mb-1">Accuracy</p>
                        <p class="text-xl font-bold">{{ $prediction->status === 'scored' ? number_format($prediction->accuracy, 1) . '%' : 'Pending' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-zinc-300 dark:text-zinc-200 uppercase tracking-widest mb-1">Submitted</p>
                        <p class="text-sm font-medium text-zinc-300 dark:text-zinc-200">
                            {{ $prediction->submitted_at ? $prediction->submitted_at->format('M j, Y H:i') : '-' }} (UTC)
                        </p>
                    </div>
                </div>
            </div>

            @if($prediction->notes)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="font-bold text-sm uppercase tracking-widest text-zinc-600 dark:text-zinc-400 mb-4">Notes</h3>
                    <p class="text-zinc-600 dark:text-zinc-300 italic whitespace-pre-line">{{ $prediction->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.layout>
