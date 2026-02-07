<x-layouts.layout>
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-red-500 via-red-400 to-red-600 dark:from-red-700 dark:via-red-600 dark:to-red-800">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative px-6 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h1 class="text-5xl font-bold text-white mb-6 text-shadow">
                    🏎️ F1 Predictions
                </h1>
                <p class="text-xl text-white mb-8 text-shadow">
                    Predict Formula 1 race outcomes and compete with friends. 
                    Track your accuracy, climb the leaderboard, and prove your F1 expertise.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ auth()->check() ? route('predict.create') : route('login') }}" wire:navigate
                        class="inline-flex items-center justify-center px-6 py-3 text-base font-medium rounded-lg bg-white text-red-600 hover:bg-red-50 shadow-md transition-colors">
                        Start Predicting
                    </a>
                    <a href="{{ route('standings', ['year' => config('f1.current_season')]) }}" wire:navigate
                        class="inline-flex items-center justify-center px-6 py-3 text-base font-medium rounded-lg border-2 border-white text-white hover:bg-white/10 transition-colors">
                        View Standings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="px-6 py-10 bg-white dark:bg-zinc-900">
        <div class="mx-auto max-w-7xl">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 mb-4">Explore F1 Predictions</h2>
                <p class="text-lg text-zinc-700 dark:text-zinc-300">
                    Navigate through different sections to make predictions, view standings, and track your performance
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Races Card -->
                <x-mary-card class="group relative hover:shadow-xl transition-all duration-300 overflow-hidden bg-card">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-blue-600 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="o-calendar" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Races</h3>
                                <p class="text-sm text-auto-muted">View race schedule</p>
                            </div>
                        </div>
                                        <p class="mb-4 text-body text-zinc-800 dark:text-zinc-200">
                    Browse upcoming and past races, view circuit information, and get ready to make your predictions.
                </p>
                        <a href="{{ route('races', ['year' => config('f1.current_season')]) }}" wire:navigate
                            class="block w-full text-center px-4 py-2 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            View Races
                        </a>
                    </div>
                </x-mary-card>

                <!-- Standings Card -->
                <x-mary-card class="group relative hover:shadow-xl transition-all duration-300 overflow-hidden bg-card">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-500 to-green-600 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="o-trophy" class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Standings</h3>
                                <p class="text-sm text-auto-muted">Driver & team rankings</p>
                            </div>
                        </div>
                        <p class="mb-4 text-body text-zinc-800 dark:text-zinc-200">
                            Check current driver and team standings, track championship points, and see who's leading the pack.
                        </p>
                        <a href="{{ route('standings', ['year' => config('f1.current_season')]) }}" wire:navigate
                            class="block w-full text-center px-4 py-2 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            View Standings
                        </a>
                    </div>
                </x-mary-card>

                <!-- Predictions Card -->
                <x-mary-card class="group relative hover:shadow-xl transition-all duration-300 overflow-hidden bg-card">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-purple-600 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="o-chart-bar" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Predictions</h3>
                                <p class="text-sm text-auto-muted">Leaderboard & accuracy</p>
                            </div>
                        </div>
                        <p class="mb-4 text-body text-zinc-800 dark:text-zinc-200">
                            See how your predictions stack up against others. Track your accuracy and climb the prediction leaderboard.
                        </p>
                        <a href="{{ route('standings.predictions', ['year' => config('f1.current_season')]) }}" wire:navigate
                            class="block w-full text-center px-4 py-2 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            View Predictions
                        </a>
                    </div>
                </x-mary-card>

                <!-- Teams Card -->
                <x-mary-card class="group relative hover:shadow-xl transition-all duration-300 overflow-hidden bg-card">
                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-orange-600 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="o-users" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Teams</h3>
                                <p class="text-sm text-auto-muted">Constructor standings</p>
                            </div>
                        </div>
                        <p class="mb-4 text-body text-zinc-800 dark:text-zinc-200">
                            Explore F1 teams, their drivers, performance statistics, and constructor championship standings.
                        </p>
                        <a href="{{ route('standings.teams', ['year' => config('f1.current_season')]) }}" wire:navigate
                            class="block w-full text-center px-4 py-2 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            View Teams
                        </a>
                    </div>
                </x-mary-card>

                <!-- Drivers Card -->
                <x-mary-card class="group relative hover:shadow-xl transition-all duration-300 overflow-hidden bg-card">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-red-600 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="o-user" class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Drivers</h3>
                                <p class="text-sm text-auto-muted">Driver standings</p>
                            </div>
                        </div>
                        <p class="mb-4 text-body text-zinc-800 dark:text-zinc-200">
                            Check driver standings, view individual statistics, and see who's leading the championship.
                        </p>
                        <a href="{{ route('standings.drivers', ['year' => config('f1.current_season')]) }}" wire:navigate
                            class="block w-full text-center px-4 py-2 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            View Drivers
                        </a>
                    </div>
                </x-mary-card>

                <!-- Countries Card -->
                <x-mary-card class="group relative hover:shadow-xl transition-all duration-300 overflow-hidden bg-card">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-indigo-600 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    <div class="relative p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="o-map-pin" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Countries</h3>
                                <p class="text-sm text-auto-muted">Geographic overview</p>
                            </div>
                        </div>
                        <p class="mb-4 text-body text-zinc-800 dark:text-zinc-200">
                            Explore F1 from a geographical perspective. See circuits, drivers, and teams by country.
                        </p>
                        <a href="{{ route('countries') }}" wire:navigate
                            class="block w-full text-center px-4 py-2 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            View Countries
                        </a>
                    </div>
                </x-mary-card>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="px-6 py-10 bg-zinc-50 dark:bg-zinc-800">
        <div class="mx-auto max-w-7xl">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 mb-4">Why F1 Predictions?</h2>
                <p class="text-lg text-zinc-700 dark:text-zinc-300">
                    Join the ultimate F1 prediction community
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-mary-icon name="o-star" class="w-8 h-8 text-red-600 dark:text-red-400" />
                    </div>
                    <h3 class="text-heading-3 mb-2">Test Your Knowledge</h3>
                    <p class="text-body text-zinc-700 dark:text-zinc-300">
                        Prove your F1 expertise by predicting race outcomes, qualifying results, and championship standings.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-mary-icon name="o-users" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-heading-3 mb-2">Compete with Friends</h3>
                    <p class="text-body text-zinc-700 dark:text-zinc-300">
                        Challenge your friends and family to see who has the best F1 prediction skills.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-mary-icon name="o-chart-bar" class="w-8 h-8 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="text-heading-3 mb-2">Track Your Progress</h3>
                    <p class="text-body text-zinc-700 dark:text-zinc-300">
                        Monitor your prediction accuracy over time and see how you improve throughout the season.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="px-6 py-10 bg-gradient-to-br from-red-500 to-red-600 dark:from-red-700 dark:to-red-800">
        <div class="mx-auto max-w-4xl text-center">
            <h2 class="text-4xl font-bold text-white mb-4 text-shadow">
                Ready to Start Predicting?
            </h2>
            <p class="text-xl text-white mb-8 text-shadow">
                Join thousands of F1 fans making predictions and competing for the top spot.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ auth()->check() ? route('predict.create') : route('register') }}" wire:navigate
                    class="inline-flex items-center justify-center px-6 py-3 text-base font-medium rounded-lg bg-white text-red-600 hover:bg-red-50 shadow-md transition-colors">
                    Get Started
                </a>
                <a href="{{ route('standings', ['year' => config('f1.current_season')]) }}" wire:navigate
                    class="inline-flex items-center justify-center px-6 py-3 text-base font-medium rounded-lg border-2 border-white text-white hover:bg-white/10 transition-colors">
                    Learn More
                </a>
            </div>
        </div>
    </div>
</x-layouts.layout>