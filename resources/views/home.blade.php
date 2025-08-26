<x-layouts.layout>
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-red-600 via-red-500 to-red-700 dark:from-red-800 dark:via-red-700 dark:to-red-900">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="relative px-6 py-24 sm:px-6 sm:py-32 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <flux:heading size="4xl" class="text-white mb-6">
                    🏎️ F1 Predictions
                </flux:heading>
                <flux:text class="text-xl text-red-100 mb-8">
                    Predict Formula 1 race outcomes and compete with friends. 
                    Track your accuracy, climb the leaderboard, and prove your F1 expertise.
                </flux:text>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <flux:button size="lg" variant="primary" class="bg-white text-red-600 hover:bg-red-50">
                        Start Predicting
                    </flux:button>
                    <flux:button size="lg" variant="outline" class="text-white border-white hover:bg-white/10">
                        View Standings
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="px-6 py-16 bg-white dark:bg-zinc-900">
        <div class="mx-auto max-w-7xl">
            <div class="text-center mb-12">
                <flux:heading size="2xl" class="mb-4">Explore F1 Predictions</flux:heading>
                <flux:text class="text-lg text-zinc-600 dark:text-zinc-400">
                    Navigate through different sections to make predictions, view standings, and track your performance
                </flux:text>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Races Card -->
                <div class="group relative bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-700 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <flux:icon icon="calendar" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4">
                                <flux:heading size="lg">Races</flux:heading>
                                <flux:text variant="muted">View race schedule</flux:text>
                            </div>
                        </div>
                        <flux:text class="mb-4">
                            Browse upcoming and past races, view circuit information, and get ready to make your predictions.
                        </flux:text>
                        <flux:button variant="outline" size="sm" class="w-full">
                            View Races
                        </flux:button>
                    </div>
                </div>

                <!-- Standings Card -->
                <div class="group relative bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-green-700 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <flux:icon icon="trophy" class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4">
                                <flux:heading size="lg">Standings</flux:heading>
                                <flux:text variant="muted">Driver & team rankings</flux:text>
                            </div>
                        </div>
                        <flux:text class="mb-4">
                            Check current driver and team standings, track championship points, and see who's leading the pack.
                        </flux:text>
                        <flux:button variant="outline" size="sm" class="w-full">
                            View Standings
                        </flux:button>
                    </div>
                </div>

                <!-- Predictions Card -->
                <div class="group relative bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-purple-700 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <flux:icon icon="chart-bar" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="ml-4">
                                <flux:heading size="lg">Predictions</flux:heading>
                                <flux:text variant="muted">Leaderboard & accuracy</flux:text>
                            </div>
                        </div>
                        <flux:text class="mb-4">
                            See how your predictions stack up against others. Track your accuracy and climb the prediction leaderboard.
                        </flux:text>
                        <flux:button variant="outline" size="sm" class="w-full">
                            View Predictions
                        </flux:button>
                    </div>
                </div>

                <!-- Teams Card -->
                <div class="group relative bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-orange-600 to-orange-700 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                                <flux:icon icon="users" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="ml-4">
                                <flux:heading size="lg">Teams</flux:heading>
                                <flux:text variant="muted">Constructor standings</flux:text>
                            </div>
                        </div>
                        <flux:text class="mb-4">
                            Explore F1 teams, their drivers, performance statistics, and constructor championship standings.
                        </flux:text>
                        <flux:button variant="outline" size="sm" class="w-full">
                            View Teams
                        </flux:button>
                    </div>
                </div>

                <!-- Drivers Card -->
                <div class="group relative bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-red-700 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                <flux:icon icon="user" class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="ml-4">
                                <flux:heading size="lg">Drivers</flux:heading>
                                <flux:text variant="muted">Driver standings</flux:text>
                            </div>
                        </div>
                        <flux:text class="mb-4">
                            Check driver standings, view individual statistics, and see who's leading the championship.
                        </flux:text>
                        <flux:button variant="outline" size="sm" class="w-full">
                            View Drivers
                        </flux:button>
                    </div>
                </div>

                <!-- Countries Card -->
                <div class="group relative bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-indigo-700 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                <flux:icon icon="map-pin" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div class="ml-4">
                                <flux:heading size="lg">Countries</flux:heading>
                                <flux:text variant="muted">Geographic overview</flux:text>
                            </div>
                        </div>
                        <flux:text class="mb-4">
                            Explore F1 from a geographical perspective. See circuits, drivers, and teams by country.
                        </flux:text>
                        <flux:button variant="outline" size="sm" class="w-full">
                            View Countries
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="px-6 py-16 bg-zinc-50 dark:bg-zinc-800">
        <div class="mx-auto max-w-7xl">
            <div class="text-center mb-12">
                <flux:heading size="2xl" class="mb-4">Why F1 Predictions?</flux:heading>
                <flux:text class="text-lg text-zinc-600 dark:text-zinc-400">
                    Join the ultimate F1 prediction community
                </flux:text>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <flux:icon icon="star" class="w-8 h-8 text-red-600 dark:text-red-400" />
                    </div>
                    <flux:heading size="lg" class="mb-2">Test Your Knowledge</flux:heading>
                    <flux:text>
                        Prove your F1 expertise by predicting race outcomes, qualifying results, and championship standings.
                    </flux:text>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <flux:icon icon="users" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                    </div>
                    <flux:heading size="lg" class="mb-2">Compete with Friends</flux:heading>
                    <flux:text>
                        Challenge your friends and family to see who has the best F1 prediction skills.
                    </flux:text>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <flux:icon icon="chart-bar" class="w-8 h-8 text-green-600 dark:text-green-400" />
                    </div>
                    <flux:heading size="lg" class="mb-2">Track Your Progress</flux:heading>
                    <flux:text>
                        Monitor your prediction accuracy over time and see how you improve throughout the season.
                    </flux:text>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="px-6 py-16 bg-red-600 dark:bg-red-700">
        <div class="mx-auto max-w-4xl text-center">
            <flux:heading size="3xl" class="text-white mb-4">
                Ready to Start Predicting?
            </flux:heading>
            <flux:text class="text-xl text-red-100 mb-8">
                Join thousands of F1 fans making predictions and competing for the top spot.
            </flux:text>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <flux:button size="lg" variant="primary" class="bg-white text-red-600 hover:bg-red-50">
                    Get Started
                </flux:button>
                <flux:button size="lg" variant="outline" class="text-white border-white hover:bg-white/10">
                    Learn More
                </flux:button>
            </div>
        </div>
    </div>
</x-layouts.layout>