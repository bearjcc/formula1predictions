<x-layouts.layout :title="$race->display_name" :headerSubtitle="'Complete race information and results for ' . $race->display_name">
    <!-- Race Overview Card -->
    <x-mary-card class="p-6 mb-8">
        <div class="flex items-start space-x-6">
            <!-- Race Logo -->
            <div class="flex-shrink-0">
                <div class="w-24 h-24 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-flag" class="w-12 h-12 text-red-600 dark:text-red-400" />
                </div>
            </div>

            <!-- Race Info -->
            <div class="flex-1">
                <h2 class="text-heading-2 mb-2">{{ $race->display_name }}</h2>
                <p class="text-auto-muted mb-4">
                    {{ $race->circuit_name ?? 'Circuit TBD' }}
                    @if($race->country) &bull; {{ $race->country }}@endif
                    @if($race->date) &bull; {{ $race->date->format('F j, Y') }}@endif
                </p>

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $race->laps ?? 'N/A' }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Laps</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $race->circuit_length ?? 'N/A' }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Length (km)</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $race->season }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Season</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $race->round }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Round</p>
                    </div>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Race Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Race Information -->
        <x-mary-card class="p-6">
            <h2 class="text-heading-3 mb-4">Race Information</h2>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Name</p>
                    <p class="font-medium">{{ $race->display_name }}</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Circuit</p>
                    <p class="font-medium">{{ $race->circuit_name ?? 'TBD' }}</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Date</p>
                    <p class="font-medium">{{ $race->date?->format('F j, Y') ?? 'TBD' }}</p>
                </div>
                @if($race->weather)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Weather</p>
                        <p class="font-medium">{{ ucfirst($race->weather) }}</p>
                    </div>
                @endif
                @if($race->temperature)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Temperature</p>
                        <p class="font-medium">{{ $race->temperature }}&deg;C</p>
                    </div>
                @endif
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Status</p>
                    @if($race->isCompleted())
                        <x-mary-badge class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            Completed
                        </x-mary-badge>
                    @elseif($race->isUpcoming())
                        <x-mary-badge class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            Upcoming
                        </x-mary-badge>
                    @else
                        <x-mary-badge class="bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200">
                            {{ ucfirst($race->status ?? 'Unknown') }}
                        </x-mary-badge>
                    @endif
                </div>
                @if($race->has_sprint)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Sprint Race</p>
                        <x-mary-badge class="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                            Yes
                        </x-mary-badge>
                    </div>
                @endif
            </div>
        </x-mary-card>

        <!-- Race Results (Podium) -->
        <x-mary-card class="p-6">
            <h2 class="text-heading-3 mb-4">Race Results</h2>
            @php $podium = $race->getPodium(); @endphp
            @if(!empty($podium))
                <div class="space-y-4">
                    @foreach($podium as $index => $result)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                            <h3 class="font-semibold mb-2">
                                @if($index === 0) P1
                                @elseif($index === 1) P2
                                @elseif($index === 2) P3
                                @endif
                            </h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $result['driver'] ?? $result['name'] ?? 'N/A' }}
                                @if(!empty($result['team'])) - {{ $result['team'] }}@endif
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-zinc-500 dark:text-zinc-400">
                    @if($race->isUpcoming())
                        Results will be available after the race.
                    @else
                        No results data available.
                    @endif
                </p>
            @endif
        </x-mary-card>
    </div>

    <!-- Full Race Results Table -->
    @php $results = $race->getResultsArray(); @endphp
    @if(!empty($results))
        <x-mary-card class="overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-heading-3">Complete Race Results</h2>
            </div>

            <div class="w-full max-w-full min-w-0 overflow-x-auto [-webkit-overflow-scrolling:touch]">
                <table class="w-full">
                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Position
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Driver
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Constructor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Points
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($results as $index => $result)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($index < 3)
                                        <x-mary-badge class="{{ $index === 0 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : ($index === 1 ? 'bg-zinc-200 text-zinc-800 dark:bg-zinc-600 dark:text-zinc-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200') }}">
                                            {{ $result['position'] ?? $index + 1 }}
                                        </x-mary-badge>
                                    @else
                                        {{ $result['position'] ?? $index + 1 }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <h3 class="font-semibold">{{ $result['driver'] ?? $result['name'] ?? 'N/A' }}</h3>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p>{{ $result['team'] ?? '' }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <h3 class="font-semibold text-green-600 dark:text-green-400">{{ $result['points'] ?? '' }}</h3>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-mary-card>
    @endif
</x-layouts.layout>
