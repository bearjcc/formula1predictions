<x-layouts.layout :title="$country->name . ' F1 Profile'" :headerSubtitle="'Formula 1 history and statistics for ' . $country->name">
    <!-- Country Overview Card -->
    <x-mary-card class="p-6 mb-8">
        <div class="flex items-start space-x-6">
            <!-- Country Flag -->
            <div class="flex-shrink-0">
                <div class="w-24 h-24 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-flag" class="w-12 h-12 text-red-600 dark:text-red-400" />
                </div>
            </div>

            <!-- Country Info -->
            <div class="flex-1">
                <h2 class="text-heading-2 mb-2">{{ $country->name }}</h2>
                @if($country->description)
                    <p class="text-auto-muted mb-4">{{ $country->description }}</p>
                @else
                    <p class="text-auto-muted mb-4">
                        Formula 1 statistics and history for {{ $country->name }}.
                    </p>
                @endif

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $country->world_championships_won ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">World Championships</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $country->teams_count ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Constructors</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $country->circuits_count ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Circuits</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $country->f1_races_hosted ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Races Hosted</p>
                    </div>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Country Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- F1 Statistics -->
        <x-mary-card class="p-6">
            <h3 class="text-heading-3 mb-4">F1 Statistics</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Races Hosted</p>
                    <p class="font-medium">{{ $country->f1_races_hosted ?? 0 }}</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">World Championships</p>
                    <p class="font-medium">{{ $country->world_championships_won ?? 0 }}</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Drivers</p>
                    <p class="font-medium">{{ $country->drivers_count ?? 0 }}</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Constructors</p>
                    <p class="font-medium">{{ $country->teams_count ?? 0 }}</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Circuits</p>
                    <p class="font-medium">{{ $country->circuits_count ?? 0 }}</p>
                </div>
            </div>
        </x-mary-card>

        <!-- Drivers from this Country -->
        <x-mary-card class="p-6">
            <h3 class="text-heading-3 mb-4">Drivers from {{ $country->name }}</h3>
            <div class="space-y-4">
                @php
                    $countryDrivers = $country->drivers()->where('is_active', true)->get();
                @endphp
                @forelse($countryDrivers as $driver)
                    <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                            <x-mary-icon name="o-user" class="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold">{{ $driver->full_name }}</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $driver->team?->team_name ?? 'No constructor' }}</p>
                        </div>
                        <a href="{{ route('driver', $driver->slug) }}">
                            <x-mary-button variant="outline" size="sm" icon="o-eye">
                                View
                            </x-mary-button>
                        </a>
                    </div>
                @empty
                    <p class="text-zinc-500 dark:text-zinc-400">No active drivers found from {{ $country->name }}.</p>
                @endforelse
            </div>
        </x-mary-card>
    </div>

    <!-- Circuits in this Country -->
    @php
        $countryCircuits = $country->circuits;
    @endphp
    @if($countryCircuits->isNotEmpty())
        <x-mary-card class="p-6 mb-8">
            <h3 class="text-heading-3 mb-4">Circuits in {{ $country->name }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($countryCircuits as $circuit)
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="o-map-pin" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold">{{ $circuit->circuit_name }}</h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $circuit->locality }}</p>
                            </div>
                        </div>
                        <a href="{{ route('circuit', $circuit->slug) }}">
                            <x-mary-button variant="outline" size="sm" class="w-full">
                                View Circuit
                            </x-mary-button>
                        </a>
                    </div>
                @endforeach
            </div>
        </x-mary-card>
    @endif
</x-layouts.layout>
