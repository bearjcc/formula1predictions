<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $year }} Standings</h1>
                <p class="text-zinc-600 dark:text-zinc-400">
                    View the complete standings for the {{ $year }} Formula 1 season
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                    Export Data
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-plus">
                    Refresh
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-8">
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <nav class="flex space-x-8">
                <a href="{{ route('standings.drivers', ['year' => $year]) }}" class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-zinc-600">
                    Driver Standings
                </a>
                <a href="{{ route('standings.teams', ['year' => $year]) }}" class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-zinc-600">
                    Team Standings
                </a>
                <a href="{{ route('standings.predictions', ['year' => $year]) }}" class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-zinc-600">
                    Prediction Standings
                </a>
            </nav>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Driver Championship -->
        <x-mary-card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold">Driver Championship</h3>
                <x-mary-icon name="o-trophy" class="w-6 h-6 text-yellow-500" />
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Leader</p>
                    <p class="font-medium">Max Verstappen</p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Points</p>
                    <p class="font-medium">454</p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Wins</p>
                    <p class="font-medium">19</p>
                </div>
            </div>
            <x-mary-button variant="outline" size="sm" class="w-full mt-4">
                View Full Standings
            </x-mary-button>
        </x-mary-card>

        <!-- Constructor Championship -->
        <x-mary-card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold">Constructor Championship</h3>
                <x-mary-icon name="o-users" class="w-6 h-6 text-blue-500" />
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Leader</p>
                    <p class="font-medium">Red Bull Racing</p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Points</p>
                    <p class="font-medium">860</p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Wins</p>
                    <p class="font-medium">21</p>
                </div>
            </div>
            <x-mary-button variant="outline" size="sm" class="w-full mt-4">
                View Full Standings
            </x-mary-button>
        </x-mary-card>

        <!-- Prediction Championship -->
        <x-mary-card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold">Prediction Championship</h3>
                <x-mary-icon name="o-chart-bar" class="w-6 h-6 text-purple-500" />
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Leader</p>
                    <p class="font-medium">F1Expert</p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Points</p>
                    <p class="font-medium">1,245</p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Accuracy</p>
                    <p class="font-medium">87.3%</p>
                </div>
            </div>
            <x-mary-button variant="outline" size="sm" class="w-full mt-4">
                View Full Standings
            </x-mary-button>
        </x-mary-card>
    </div>

    <!-- Recent Results -->
    <x-mary-card>
        <h3 class="text-xl font-bold mb-4">Recent Race Results</h3>
        <div class="space-y-4">
            <!-- Recent Race 1 -->
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">Abu Dhabi Grand Prix</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">November 26, 2023</p>
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
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">São Paulo Grand Prix</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">November 5, 2023</p>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="font-semibold">Winner: Max Verstappen</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Red Bull Racing</p>
                </div>
            </div>

            <!-- Recent Race 3 -->
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold">Mexico City Grand Prix</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">October 29, 2023</p>
                    </div>
                </div>
                <div class="text-right">
                    <h4 class="font-semibold">Winner: Max Verstappen</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Red Bull Racing</p>
                </div>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>
