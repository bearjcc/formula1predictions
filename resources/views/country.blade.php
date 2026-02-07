<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 mb-2">{{ ucfirst($slug) }} F1 Profile</h1>
                <p class="text-auto-muted">
                    Formula 1 history and statistics for {{ ucfirst($slug) }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" name="o-map">
                    Map View
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" name="o-star">
                    Follow Country
                </x-mary-button>
            </div>
        </div>
    </div>

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
                <h2 class="text-heading-2 mb-2">{{ ucfirst($slug) }}</h2>
                <p class="text-auto-muted mb-4">
                    Home to some of the most successful teams and drivers in Formula 1 history.
                </p>

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">15</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">World Champions</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">8</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Constructors</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">3</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Circuits</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">73</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Grand Prix Races</p>
                    </div>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Country Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- F1 History -->
        <x-mary-card class="p-6">
            <h3 class="text-heading-3 mb-4">F1 History</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">First Grand Prix</p>
                    <p class="font-medium">1950</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">First Champion</p>
                    <p class="font-medium">Mike Hawthorn (1958)</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Most Recent Champion</p>
                    <p class="font-medium">Lewis Hamilton (2020)</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Championships</p>
                    <p class="font-medium">20</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Race Wins</p>
                    <p class="font-medium">303</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Podiums</p>
                    <p class="font-medium">1,247</p>
                </div>
            </div>
        </x-mary-card>

        <!-- Current Drivers -->
        <x-mary-card class="p-6">
            <h3 class="text-heading-3 mb-4">Current Drivers</h3>
            <div class="space-y-4">
                <!-- Driver 1 -->
                <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <x-mary-icon name="o-user" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Lewis Hamilton</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Mercedes</p>
                    </div>
                    <x-mary-button variant="outline" size="sm" name="o-eye">
                        View
                    </x-mary-button>
                </div>

                <!-- Driver 2 -->
                <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <x-mary-icon name="o-user" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">George Russell</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Mercedes</p>
                    </div>
                    <x-mary-button variant="outline" size="sm" name="o-eye">
                        View
                    </x-mary-button>
                </div>

                <!-- Driver 3 -->
                <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <x-mary-icon name="o-user" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Lando Norris</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">McLaren</p>
                    </div>
                    <x-mary-button variant="outline" size="sm" name="o-eye">
                        View
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>
    </div>

    <!-- Circuits -->
    <x-mary-card class="p-6 mb-8">
        <h3 class="text-heading-3 mb-4">Circuits in {{ ucfirst($slug) }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Circuit 1 -->
            <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-map-pin" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">Silverstone</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">British Grand Prix</p>
                    </div>
                </div>
                <x-mary-button variant="outline" size="sm" class="w-full">
                    View Circuit
                </x-mary-button>
            </div>

            <!-- Circuit 2 -->
            <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-map-pin" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">Brands Hatch</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Historic Circuit</p>
                    </div>
                </div>
                <x-mary-button variant="outline" size="sm" class="w-full">
                    View Circuit
                </x-mary-button>
            </div>

            <!-- Circuit 3 -->
            <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-map-pin" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">Donington Park</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Historic Circuit</p>
                    </div>
                </div>
                <x-mary-button variant="outline" size="sm" class="w-full">
                    View Circuit
                </x-mary-button>
            </div>
        </div>
    </x-mary-card>

    <!-- Recent Race Results -->
    <x-mary-card class="p-6 mb-8">
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
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">July 9, 2023 - Silverstone</p>
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
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">July 3, 2022 - Silverstone</p>
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
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">July 18, 2021 - Silverstone</p>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="font-semibold">Winner: Lewis Hamilton</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Mercedes</p>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Country Statistics -->
    <x-mary-card class="p-6">
        <h3 class="text-heading-3 mb-4">{{ ucfirst($slug) }} F1 Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Wins -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">303</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Race Wins</p>
            </div>

            <!-- Championships -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">20</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">World Championships</p>
            </div>

            <!-- Podiums -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">1,247</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Podiums</p>
            </div>

            <!-- Poles -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">285</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Pole Positions</p>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>
