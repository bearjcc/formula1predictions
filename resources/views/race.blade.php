<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 mb-2">Race {{ $id ?? $slug ?? 'Details' }}</h1>
                <p class="text-auto-muted">
                    Complete race information and results for {{ isset($id) && $id ? "Race #{$id}" : ($slug ?? 'this race') }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                    Statistics
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-star">
                    Make Prediction
                </x-mary-button>
            </div>
        </div>
    </div>

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
                <h2 class="text-heading-2 mb-2">Race {{ $id ?? $slug ?? 'Details' }}</h2>
                <p class="text-auto-muted mb-4">
                    Silverstone Circuit • United Kingdom • July 9, 2023
                </p>

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">52</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Laps</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">306.198</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Distance (km)</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">1:24.303</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Fastest Lap</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">20</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Drivers</p>
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
                    <p class="font-medium">{{ isset($id) && $id ? "Race #{$id}" : ($slug ?? 'Race Details') }}</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Circuit</p>
                    <p class="font-medium">Silverstone Circuit</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Date</p>
                    <p class="font-medium">July 9, 2023</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Weather</p>
                    <p class="font-medium">Partly Cloudy</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Temperature</p>
                    <p class="font-medium">22°C</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Status</p>
                    <x-mary-badge class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        Completed
                    </x-mary-badge>
                </div>
            </div>
        </x-mary-card>

        <!-- Race Results -->
        <x-mary-card class="p-6">
            <h2 class="text-heading-3 mb-4">Race Results</h2>
            <div class="space-y-4">
                <!-- Winner -->
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <h3 class="font-semibold mb-2">🏆 Winner</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Max Verstappen - Red Bull Racing</p>
                </div>
                <!-- Second Place -->
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <h3 class="font-semibold mb-2">🥈 Second</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Lando Norris - McLaren</p>
                </div>
                <!-- Third Place -->
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <h3 class="font-semibold mb-2">🥉 Third</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Lewis Hamilton - Mercedes</p>
                </div>
            </div>
        </x-mary-card>
    </div>

    <!-- Race Results Table -->
    <x-mary-card class="overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-heading-3">Complete Race Results</h2>
        </div>
        
        <div class="overflow-x-auto">
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
                            Team
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Time
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Points
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    <!-- Position 1 -->
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-mary-badge class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                1
                            </x-mary-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h3 class="font-semibold">Max Verstappen</h3>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>Red Bull Racing</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>1:25:16.938</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h3 class="font-semibold text-green-600 dark:text-green-400">25</h3>
                        </td>
                    </tr>

                    <!-- Position 2 -->
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-mary-badge class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                2
                            </x-mary-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h3 class="font-semibold">Lando Norris</h3>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>McLaren</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>+3.798</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h3 class="font-semibold text-green-600 dark:text-green-400">18</h3>
                        </td>
                    </tr>

                    <!-- Position 3 -->
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-mary-badge class="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                3
                            </x-mary-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h3 class="font-semibold">Lewis Hamilton</h3>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>Mercedes</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>+6.783</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h3 class="font-semibold text-green-600 dark:text-green-400">15</h3>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-mary-card>

    <!-- Race Statistics -->
    <x-mary-card class="p-6">
        <h2 class="text-heading-3 mb-4">Race Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Fastest Lap -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h3 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">1:24.303</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Fastest Lap (Max Verstappen)</p>
            </div>

            <!-- Lead Changes -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h3 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">2</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Lead Changes</p>
            </div>

            <!-- Safety Cars -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h3 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">1</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Safety Car Periods</p>
            </div>

            <!-- Retirements -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h3 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">3</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Driver Retirements</p>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>
