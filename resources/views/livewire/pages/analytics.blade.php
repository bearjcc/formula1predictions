@layoutData(['title' => 'Analytics Dashboard', 'headerSubtitle' => 'Comprehensive data visualization and insights for Formula 1 predictions'])

<div>
    {{-- #region Page Header --}}
    <div class="mb-8">
        <div class="flex justify-end">
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

    {{-- #region Stats Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-mary-icon name="o-chart-bar" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Predictions</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $totalPredictions ?? 0 }}
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
                                {{ $activeUsers ?? 0 }}
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
                                {{ $racesCompleted ?? 0 }}
                            </p>
                        </div>
                    </div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <x-mary-icon name="o-chart-bar" class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Avg Accuracy</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $avgAccuracy ?? 0 }}%
                            </p>
                        </div>
                    </div>
                </div>
    </div>

    {{-- #region Charts Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-6 lg:mb-8">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 min-h-[320px]">
            @livewire('charts.prediction-accuracy-chart', ['season' => $selectedSeason ?? 2024])
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 min-h-[320px]">
            @livewire('charts.standings-chart', ['season' => $selectedSeason ?? 2024])
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 min-h-[320px]">
            @livewire('charts.driver-consistency-chart', ['season' => $selectedSeason ?? 2024])
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 min-h-[320px]">
            @livewire('charts.points-progression-chart', ['season' => $selectedSeason ?? 2024])
        </div>
    </div>

    {{-- #region Additional Analytics --}}
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <h2 class="text-heading-3 mb-4">Detailed Analytics</h2>
        <p class="text-auto-muted">
            Additional analytics and insights will be displayed here as more data becomes available.
        </p>
    </div>
</div>
