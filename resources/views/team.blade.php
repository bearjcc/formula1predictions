<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ ucfirst($slug) }} Team Profile</h1>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Complete team information and statistics for {{ ucfirst($slug) }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                    Statistics
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-star">
                    Follow Team
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Team Overview Card -->
    <x-mary-card class="mb-8">
        <div class="flex items-start space-x-6">
            <!-- Team Logo -->
            <div class="flex-shrink-0">
                <div class="w-24 h-24 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-users" class="w-12 h-12 text-red-600 dark:text-red-400" />
                </div>
            </div>

            <!-- Team Info -->
            <div class="flex-1">
                <h2 class="text-2xl font-bold mb-2">{{ ucfirst($slug) }} Racing</h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    Based in Milton Keynes, United Kingdom. Founded in 2005.
                </p>

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">6</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">118</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">285</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Podiums</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">95</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Pole Positions</p>
                    </div>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Team Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Current Drivers -->
        <x-mary-card>
            <h3 class="text-xl font-bold mb-4">Current Drivers</h3>
            <div class="space-y-4">
                <!-- Driver 1 -->
                <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <x-mary-icon name="o-user" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Max Verstappen</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Driver #1</p>
                    </div>
                    <x-mary-button variant="outline" size="sm" icon="o-eye">
                        View
                    </x-mary-button>
                </div>

                <!-- Driver 2 -->
                <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <x-mary-icon name="o-user" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Sergio Pérez</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Driver #2</p>
                    </div>
                    <x-mary-button variant="outline" size="sm" icon="o-eye">
                        View
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>

        <!-- Team Principal -->
        <x-mary-card>
            <h3 class="text-xl font-bold mb-4">Team Principal</h3>
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                    <x-mary-icon name="o-user" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h4 class="font-semibold">Christian Horner</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Team Principal & CEO</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Since 2005</p>
                </div>
            </div>
        </x-mary-card>
    </div>

    <!-- Recent Performance -->
    <x-mary-card class="mb-8">
        <h3 class="text-xl font-bold mb-4">Recent Performance</h3>
        <div class="space-y-4">
            <!-- Recent Race 1 -->
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-trophy" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">Abu Dhabi Grand Prix</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">November 26, 2023</p>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="font-semibold">1st Place</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Max Verstappen</p>
                </div>
            </div>

            <!-- Recent Race 2 -->
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-trophy" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">São Paulo Grand Prix</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">November 5, 2023</p>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="font-semibold">1st Place</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Max Verstappen</p>
                </div>
            </div>

            <!-- Recent Race 3 -->
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-trophy" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">Mexico City Grand Prix</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">October 29, 2023</p>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="font-semibold">1st Place</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Max Verstappen</p>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Team Statistics -->
    <x-mary-card>
        <h3 class="text-xl font-bold mb-4">Season Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Points -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">860</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Constructor Points</p>
            </div>

            <!-- Wins -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">21</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Wins</p>
            </div>

            <!-- Podiums -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">30</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Podium Finishes</p>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>
