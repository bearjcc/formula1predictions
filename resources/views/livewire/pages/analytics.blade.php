<?php

use App\Services\ChartDataService;
use function Livewire\Volt\{state, computed};

state(['selectedSeason' => 2024]);

$chartService = computed(fn() => app(ChartDataService::class));

?>

<x-layouts.app>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <!-- Header -->
        <div class="bg-white dark:bg-zinc-800 shadow-sm border-b border-zinc-200 dark:border-zinc-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            Analytics Dashboard
                        </h1>
                        <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                            Comprehensive data visualization and insights for Formula 1 predictions
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <label for="season-select" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Season:
                            </label>
                            <select 
                                id="season-select"
                                wire:model.live="selectedSeason"
                                class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 lg:py-8">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-mary-icon name="o-chart-bar" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Predictions</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ \App\Models\Prediction::where('season', $selectedSeason)->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-mary-icon name="o-users" class="w-8 h-8 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Active Users</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ \App\Models\User::whereHas('predictions', function($q) use ($selectedSeason) { $q->where('season', $selectedSeason); })->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-mary-icon name="o-trophy" class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Races Completed</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ \App\Models\Races::where('season', $selectedSeason)->whereNotNull('results')->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-mary-icon name="o-target" class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Avg Accuracy</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ number_format(\App\Models\Prediction::where('season', $selectedSeason)->whereNotNull('accuracy')->avg('accuracy') ?? 0, 1) }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                <!-- Standings Progression Chart -->
                <div class="lg:col-span-2">
                    <livewire:charts.standings-chart :chart-type="'driver'" :season="$selectedSeason" />
                </div>

                <!-- Prediction Accuracy Chart -->
                <div class="lg:col-span-2">
                    <livewire:charts.prediction-accuracy-chart :chart-type="'user-comparison'" :season="$selectedSeason" />
                </div>

                <!-- Driver Performance Comparison -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        Driver Performance Comparison
                    </h3>
                    <div class="space-y-4">
                        @php
                            $driverData = $chartService->getDriverPerformanceComparison($selectedSeason);
                        @endphp
                        @foreach(array_slice($driverData, 0, 10) as $driver)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $driver['driver'] }}</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $driver['team'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $driver['points'] }} pts</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">P{{ $driver['position'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Team Performance Comparison -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        Team Performance Comparison
                    </h3>
                    <div class="space-y-4">
                        @php
                            $teamData = $chartService->getTeamPerformanceComparison($selectedSeason);
                        @endphp
                        @foreach(array_slice($teamData, 0, 10) as $team)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $team['team'] }}</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $team['wins'] }} wins, {{ $team['podiums'] }} podiums</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $team['points'] }} pts</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">P{{ $team['position'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Additional Analytics -->
            <div class="mt-6 lg:mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                <!-- Race Accuracy Trends -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        Race Prediction Accuracy Trends
                    </h3>
                    <div class="space-y-3">
                        @php
                            $raceAccuracyData = $chartService->getRacePredictionAccuracyByRace($selectedSeason);
                        @endphp
                        @foreach(array_slice($raceAccuracyData, 0, 8) as $race)
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $race['race'] }}</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $race['total_predictions'] }} predictions</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $race['avg_accuracy'] }}%</p>
                                    <div class="w-20 bg-zinc-200 dark:bg-zinc-600 rounded-full h-2 mt-1">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $race['avg_accuracy'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- User Leaderboard -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        User Prediction Leaderboard
                    </h3>
                    <div class="space-y-3">
                        @php
                            $userComparisonData = $chartService->getPredictionAccuracyComparison($selectedSeason);
                        @endphp
                        @foreach(array_slice($userComparisonData, 0, 8) as $index => $user)
                            <div class="flex items-center justify-between p-3 {{ $index === 0 ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-zinc-50 dark:bg-zinc-700' }} rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-600 flex items-center justify-center">
                                        <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300">{{ $index + 1 }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user['user'] }}</p>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user['total_predictions'] }} predictions</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $user['avg_accuracy'] }}%</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user['total_score'] }} pts</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
