<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 mb-2">F1 Countries</h1>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Explore Formula 1 history and statistics by country
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-map">
                    Map View
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-plus">
                    Compare Countries
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <x-mary-card class="mb-8">
        <h3 class="text-heading-3 mb-4">Filters</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Region</label>
                <x-mary-select>
                    <option value="">All Regions</option>
                    <option value="europe">Europe</option>
                    <option value="americas">Americas</option>
                    <option value="asia">Asia</option>
                    <option value="oceania">Oceania</option>
                </x-mary-select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Championships</label>
                <x-mary-select>
                    <option value="">All Countries</option>
                    <option value="1-5">1-5 Championships</option>
                    <option value="6-10">6-10 Championships</option>
                    <option value="10+">10+ Championships</option>
                </x-mary-select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                <x-mary-select>
                    <option value="">All Countries</option>
                    <option value="active">Active in F1</option>
                    <option value="historic">Historic</option>
                </x-mary-select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
                <x-mary-input placeholder="Search countries..." icon="o-magnifying-glass" />
            </div>
        </div>
    </x-mary-card>

    <!-- Countries Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Country Card 1 -->
        <x-mary-card class="overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">United Kingdom</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Europe</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">20</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">303</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <x-mary-button variant="outline" size="sm" icon="o-eye">
                        View Details
                    </x-mary-button>
                    <x-mary-button variant="ghost" size="sm" icon="o-star">
                        Favorite
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>

        <!-- Country Card 2 -->
        <x-mary-card class="overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Germany</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Europe</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">12</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">179</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <x-mary-button variant="outline" size="sm" icon="o-eye">
                        View Details
                    </x-mary-button>
                    <x-mary-button variant="ghost" size="sm" icon="o-star">
                        Favorite
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>

        <!-- Country Card 3 -->
        <x-mary-card class="overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Brazil</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Americas</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">8</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">101</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <x-mary-button variant="outline" size="sm" icon="o-eye">
                        View Details
                    </x-mary-button>
                    <x-mary-button variant="ghost" size="sm" icon="o-star">
                        Favorite
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>

        <!-- Country Card 4 -->
        <x-mary-card class="overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Italy</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Europe</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">15</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">243</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <x-mary-button variant="outline" size="sm" icon="o-eye">
                        View Details
                    </x-mary-button>
                    <x-mary-button variant="ghost" size="sm" icon="o-star">
                        Favorite
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>

        <!-- Country Card 5 -->
        <x-mary-card class="overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Netherlands</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Europe</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">3</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">54</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <x-mary-button variant="outline" size="sm" icon="o-eye">
                        View Details
                    </x-mary-button>
                    <x-mary-button variant="ghost" size="sm" icon="o-star">
                        Favorite
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>

        <!-- Country Card 6 -->
        <x-mary-card class="overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                        <x-mary-icon name="o-flag" class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Australia</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Oceania</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">4</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-green-600 dark:text-green-400">33</h4>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <x-mary-button variant="outline" size="sm" icon="o-eye">
                        View Details
                    </x-mary-button>
                    <x-mary-button variant="ghost" size="sm" icon="o-star">
                        Favorite
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between">
        <p class="text-zinc-600 dark:text-zinc-400">
            Showing 1-6 of 25 countries
        </p>

        <div class="flex items-center space-x-2">
            <x-mary-button variant="outline" size="sm" icon="o-chevron-left">
                Previous
            </x-mary-button>
            <x-mary-button variant="outline" size="sm">1</x-mary-button>
            <x-mary-button variant="primary" size="sm">2</x-mary-button>
            <x-mary-button variant="outline" size="sm">3</x-mary-button>
            <x-mary-button variant="outline" size="sm">4</x-mary-button>
            <x-mary-button variant="outline" size="sm" icon="o-chevron-right">
                Next
            </x-mary-button>
        </div>
    </div>
</x-layouts.layout>
