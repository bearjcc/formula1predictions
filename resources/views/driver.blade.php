<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ ucfirst(str_replace('-', ' ', $slug)) }} Driver Profile</h1>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Complete driver information and statistics for {{ ucfirst(str_replace('-', ' ', $slug)) }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                    Statistics
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-star">
                    Follow Driver
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Driver Overview Card -->
    <x-mary-card class="mb-8">
        <div class="flex items-start space-x-6">
            <!-- Driver Photo -->
            <div class="flex-shrink-0">
                <div class="w-24 h-24 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                    <x-mary-icon name="o-user" class="w-12 h-12 text-red-600 dark:text-red-400" />
                </div>
            </div>

            <!-- Driver Info -->
            <div class="flex-1">
                <h2 class="text-2xl font-bold mb-2">{{ ucfirst(str_replace('-', ' ', $slug)) }}</h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    Red Bull Racing • Netherlands • Driver #1
                </p>

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">3</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">54</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">98</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Podiums</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">32</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Pole Positions</p>
                    </div>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Driver Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Personal Information -->
        <x-mary-card>
            <h3 class="text-xl font-bold mb-4">Personal Information</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Full Name</p>
                    <p class="font-medium">{{ ucfirst(str_replace('-', ' ', $slug)) }}</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Date of Birth</p>
                    <p class="font-medium">September 30, 1997</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Nationality</p>
                    <p class="font-medium">Dutch</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Place of Birth</p>
                    <p class="font-medium">Hasselt, Belgium</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Height</p>
                    <p class="font-medium">1.81 m (5 ft 11 in)</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Weight</p>
                    <p class="font-medium">72 kg (159 lb)</p>
                </div>
            </div>
        </x-mary-card>

        <!-- Current Team -->
        <x-mary-card>
            <h3 class="text-xl font-bold mb-4">Current Team</h3>
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-users" class="w-8 h-8 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h4 class="font-semibold">Red Bull Racing</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Since 2016</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Driver #1</p>
                </div>
            </div>
        </x-mary-card>
    </div>

    <!-- Recent Race Results -->
    <x-mary-card class="mb-8">
        <h3 class="text-xl font-bold mb-4">Recent Race Results</h3>
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
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">25 Points</p>
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
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">25 Points</p>
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
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">25 Points</p>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Season Statistics -->
    <x-mary-card>
        <h3 class="text-xl font-bold mb-4">2023 Season Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Points -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">454</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Championship Points</p>
            </div>

            <!-- Wins -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">19</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Wins</p>
            </div>

            <!-- Podiums -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">21</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Podium Finishes</p>
            </div>

            <!-- Poles -->
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">12</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Pole Positions</p>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>
