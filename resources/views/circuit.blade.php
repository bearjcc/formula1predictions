<x-layouts.layout :title="$circuit->circuit_name" :headerSubtitle="'Complete circuit information and statistics for ' . $circuit->circuit_name">
    <!-- Circuit Overview Card -->
    <x-mary-card class="mb-8">
        <div class="flex items-start space-x-6">
            <!-- Circuit Logo -->
            <div class="flex-shrink-0">
                <div class="w-24 h-24 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-map-pin" class="w-12 h-12 text-blue-600 dark:text-blue-400" />
                </div>
            </div>

            <!-- Circuit Info -->
            <div class="flex-1">
                <h2 class="text-heading-2 mb-2">{{ $circuit->circuit_name }}</h2>
                <p class="text-auto-muted mb-4">
                    Located in {{ $circuit->location }}.
                    @if($circuit->first_grand_prix) First Grand Prix in {{ $circuit->first_grand_prix }}.@endif
                </p>

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $circuit->circuit_length ?? 'N/A' }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Track Length (km)</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $circuit->laps ?? 'N/A' }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Laps</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $circuit->capacity ? number_format($circuit->capacity) : 'N/A' }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Capacity</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $circuit->first_grand_prix ?? 'N/A' }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">First Grand Prix</p>
                    </div>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Circuit Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Circuit Information -->
        <x-mary-card>
            <h3 class="text-heading-3 mb-4">Circuit Information</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Full Name</p>
                    <p class="font-medium">{{ $circuit->circuit_name }}</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Location</p>
                    <p class="font-medium">{{ $circuit->location }}</p>
                </div>
                @if($circuit->first_grand_prix)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">First Grand Prix</p>
                        <p class="font-medium">{{ $circuit->first_grand_prix }}</p>
                    </div>
                @endif
                @if($circuit->circuit_length)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Track Length</p>
                        <p class="font-medium">{{ $circuit->circuit_length }} km</p>
                    </div>
                @endif
                @if($circuit->laps)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Laps</p>
                        <p class="font-medium">{{ $circuit->laps }}</p>
                    </div>
                @endif
            </div>
        </x-mary-card>

        <!-- Lap Record -->
        <x-mary-card>
            <h3 class="text-heading-3 mb-4">Lap Record</h3>
            <div class="space-y-4">
                @if($circuit->lap_record_time)
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <h4 class="font-semibold mb-2">Lap Record</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $circuit->lap_record_time }}
                            @if($circuit->lap_record_driver) - {{ $circuit->lap_record_driver }}@endif
                            @if($circuit->lap_record_year) ({{ $circuit->lap_record_year }})@endif
                        </p>
                    </div>
                @else
                    <p class="text-zinc-500 dark:text-zinc-400">No lap record data available.</p>
                @endif
            </div>
        </x-mary-card>
    </div>

    <!-- Recent Races at this Circuit -->
    @php
        $recentRaces = $circuit->races()->orderBy('date', 'desc')->limit(5)->get();
    @endphp
    @if($recentRaces->isNotEmpty())
        <x-mary-card class="mb-8">
            <h3 class="text-heading-3 mb-4">Recent Races</h3>
            <div class="space-y-4">
                @foreach($recentRaces as $race)
                    <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="o-flag" class="w-5 h-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold">{{ $race->season }} {{ $race->race_name }}</h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $race->date?->format('F j, Y') ?? 'Date TBD' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @php $winner = $race->getWinner(); @endphp
                            @if($winner)
                                <h4 class="font-semibold">Winner: {{ $winner['driver'] ?? $winner['name'] ?? 'N/A' }}</h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $winner['team'] ?? '' }}</p>
                            @else
                                <x-mary-badge class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ ucfirst($race->status ?? 'upcoming') }}
                                </x-mary-badge>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-mary-card>
    @endif
</x-layouts.layout>
