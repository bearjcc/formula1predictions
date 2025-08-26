<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Dashboard</h1>
                <p class="text-auto-muted">
                    Your personalized Formula 1 prediction dashboard
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                    View Stats
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-plus">
                    Make Prediction
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Predictions -->
        <x-mary-card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-auto-muted">Total Predictions</p>
                    <h2 class="text-2xl font-bold text-green-600 dark:text-green-400">156</h2>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                                <x-mary-icon name="o-chart-bar" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </x-mary-card>

        <!-- Accuracy -->
        <x-mary-card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-auto-muted">Prediction Accuracy</p>
                    <h2 class="text-2xl font-bold text-blue-600 dark:text-blue-400">87.3%</h2>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <x-mary-icon name="o-chart-bar" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </x-mary-card>

        <!-- Total Points -->
        <x-mary-card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-auto-muted">Total Points</p>
                    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">1,245</h2>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                                <x-mary-icon name="o-trophy" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
        </x-mary-card>

        <!-- Current Rank -->
        <x-mary-card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-auto-muted">Current Rank</p>
                    <h2 class="text-2xl font-bold text-orange-600 dark:text-orange-400">#1</h2>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                                                <x-mary-icon name="o-star" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                </div>
            </div>
        </x-mary-card>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Upcoming Races -->
        <x-mary-card class="lg:col-span-2 p-6">
            <h3 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-white">Upcoming Races</h3>
            <div class="space-y-4">
                <!-- Race 1 -->
                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <x-mary-icon name="o-flag" class="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <h4 class="font-semibold">British Grand Prix</h4>
                            <p class="text-sm text-auto-muted">July 7, 2024 - Silverstone</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-mary-button variant="outline" size="sm" icon="o-eye">
                            View
                        </x-mary-button>
                        <x-mary-button variant="primary" size="sm" icon="o-plus">
                            Predict
                        </x-mary-button>
                    </div>
                </div>

                <!-- Race 2 -->
                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <x-mary-icon name="o-flag" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h4 class="font-semibold">Hungarian Grand Prix</h4>
                            <p class="text-sm text-auto-muted">July 21, 2024 - Hungaroring</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-mary-button variant="outline" size="sm" icon="o-eye">
                            View
                        </x-mary-button>
                        <x-mary-button variant="primary" size="sm" icon="o-plus">
                            Predict
                        </x-mary-button>
                    </div>
                </div>

                <!-- Race 3 -->
                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <x-mary-icon name="o-flag" class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h4 class="font-semibold">Belgian Grand Prix</h4>
                            <p class="text-sm text-auto-muted">July 28, 2024 - Spa-Francorchamps</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-mary-button variant="outline" size="sm" icon="o-eye">
                            View
                        </x-mary-button>
                        <x-mary-button variant="primary" size="sm" icon="o-plus">
                            Predict
                        </x-mary-button>
                    </div>
                </div>
            </div>
        </x-mary-card>

        <!-- Leaderboard -->
        <x-mary-card class="p-6">
            <h3 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-white">Leaderboard</h3>
            <div class="space-y-3">
                <!-- Position 1 -->
                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <x-mary-badge class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            1
                        </x-mary-badge>
                        <div>
                            <h4 class="font-semibold">You</h4>
                            <p class="text-sm text-auto-muted">1,245 pts</p>
                        </div>
                    </div>
                </div>

                <!-- Position 2 -->
                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <x-mary-badge class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                            2
                        </x-mary-badge>
                        <div>
                            <h4 class="font-semibold">RacingFan2023</h4>
                            <p class="text-sm text-auto-muted">1,187 pts</p>
                        </div>
                    </div>
                </div>

                <!-- Position 3 -->
                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <x-mary-badge class="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                            3
                        </x-mary-badge>
                        <div>
                            <h4 class="font-semibold">PredictorPro</h4>
                            <p class="text-sm text-auto-muted">1,156 pts</p>
                        </div>
                    </div>
                </div>

                <!-- Position 4 -->
                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <x-mary-badge class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            4
                        </x-mary-badge>
                        <div>
                            <h4 class="font-semibold">F1Expert</h4>
                            <p class="text-sm text-auto-muted">1,089 pts</p>
                        </div>
                    </div>
                </div>

                <!-- Position 5 -->
                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <x-mary-badge class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            5
                        </x-mary-badge>
                        <div>
                            <h4 class="font-semibold">SpeedDemon</h4>
                            <p class="text-sm text-auto-muted">987 pts</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-mary-card>
    </div>

    <!-- Recent Activity -->
    <x-mary-card class="p-6 mb-8">
        <h3 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-white">Recent Activity</h3>
        <div class="space-y-4">
            <!-- Activity 1 -->
            <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                    <x-mary-icon name="o-check" class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold">Perfect Prediction!</h4>
                    <p class="text-sm text-auto-muted">You correctly predicted the Monaco GP podium</p>
                </div>
                <p class="text-sm text-zinc-500">2 hours ago</p>
            </div>

            <!-- Activity 2 -->
            <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                    <x-mary-icon name="o-plus" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold">New Prediction</h4>
                    <p class="text-sm text-auto-muted">You made a prediction for the Canadian GP</p>
                </div>
                <p class="text-sm text-zinc-500">1 day ago</p>
            </div>

            <!-- Activity 3 -->
            <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                                    <x-mary-icon name="o-trophy" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold">Leaderboard Climb</h4>
                    <p class="text-sm text-auto-muted">You moved up to #1 in the leaderboard</p>
                </div>
                <p class="text-sm text-zinc-500">3 days ago</p>
            </div>
        </div>
    </x-mary-card>

    <!-- Quick Actions -->
    <x-mary-card class="p-6">
        <h3 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-white">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-mary-button variant="outline" size="lg" icon="o-plus" class="h-20">
                Make Prediction
            </x-mary-button>
            <x-mary-button variant="outline" size="lg" icon="o-chart-bar" class="h-20">
                View Statistics
            </x-mary-button>
            <x-mary-button variant="outline" size="lg" icon="o-trophy" class="h-20">
                Leaderboard
            </x-mary-button>
            <x-mary-button variant="outline" size="lg" icon="o-calendar" class="h-20">
                Race Schedule
            </x-mary-button>
        </div>
    </x-mary-card>
</x-layouts.layout>
