<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 mb-2">{{ ucfirst($slug) }} Circuit</h1>
                <p class="text-auto-muted">
                    Complete circuit information and statistics for {{ ucfirst($slug) }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-map">
                    Track Map
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-star">
                    Favorite Circuit
                </x-mary-button>
            </div>
        </div>
    </div>

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
                <h2 class="text-heading-2 mb-2">{{ ucfirst($slug) }} Circuit</h2>
                <p class="text-auto-muted mb-4">
                    Located in {{ ucfirst($slug) }}, United Kingdom. Home of the British Grand Prix.
                </p>

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">5.891</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Track Length (km)</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">18</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Corners</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">52</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Laps</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">306.198</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Distance (km)</p>
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
                    <p class="font-medium">{{ ucfirst($slug) }} Circuit</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Location</p>
                    <p class="font-medium">{{ ucfirst($slug) }}, United Kingdom</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Opened</p>
                    <p class="font-medium">1948</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">First Grand Prix</p>
                    <p class="font-medium">1950</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Circuit Type</p>
                    <p class="font-medium">Permanent</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Surface</p>
                    <p class="font-medium">Asphalt</p>
                </div>
            </div>
        </x-mary-card>

        <!-- Lap Record -->
        <x-mary-card>
            <h3 class="text-heading-3 mb-4">Lap Records</h3>
            <div class="space-y-4">
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <h4 class="font-semibold mb-2">Qualifying Record</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">1:24.303 - Max Verstappen (2023)</p>
                </div>
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <h4 class="font-semibold mb-2">Race Lap Record</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">1:27.097 - Lewis Hamilton (2020)</p>
                </div>
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <h4 class="font-semibold mb-2">Fastest Lap</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">1:24.303 - Max Verstappen (2023)</p>
                </div>
            </div>
        </x-mary-card>
    </div>

    <!-- Recent Race Results -->
    <x-mary-card class="mb-8">
        <h3 class="text-heading-3 mb-4">Recent Race Results</h3>
        <div class="space-y-4">
            <!-- Recent Race 1 -->
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">2023 British Grand Prix</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">July 9, 2023</p>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="font-semibold">Winner: Max Verstappen</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Red Bull Racing</p>
                </div>
            </div>

            <!-- Recent Race 2 -->
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">2022 British Grand Prix</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">July 3, 2022</p>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="font-semibold">Winner: Carlos Sainz</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Ferrari</p>
                </div>
            </div>

            <!-- Recent Race 3 -->
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">2021 British Grand Prix</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">July 18, 2021</p>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="font-semibold">Winner: Lewis Hamilton</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Mercedes</p>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Circuit Statistics -->
    <x-mary-card>
        <h3 class="text-heading-3 mb-4">Circuit Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Most Wins -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">8</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Most Wins (Lewis Hamilton)</p>
            </div>

            <!-- Most Poles -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">8</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Most Poles (Lewis Hamilton)</p>
            </div>

            <!-- Total Races -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">73</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Grand Prix Races</p>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>
