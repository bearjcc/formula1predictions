<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $year }} Prediction Standings</h1>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Prediction championship standings for the {{ $year }} Formula 1 season
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                    Export
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-plus">
                    Make Prediction
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <x-mary-card class="p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Time Period</label>
                <x-mary-select>
                    <option value="">All Time</option>
                    <option value="season">This Season</option>
                    <option value="month">This Month</option>
                    <option value="week">This Week</option>
                </x-mary-select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Accuracy Range</label>
                <x-mary-select>
                    <option value="">All Accuracies</option>
                    <option value="90-100">90-100%</option>
                    <option value="80-89">80-89%</option>
                    <option value="70-79">70-79%</option>
                    <option value="60-69">60-69%</option>
                </x-mary-select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                <x-mary-select>
                    <option value="">All Users</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </x-mary-select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
                <x-mary-input placeholder="Search users..." icon="o-magnifying-glass" />
            </div>
        </div>
    </x-mary-card>

    <!-- Prediction Standings Table -->
    <x-mary-card class="overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-xl font-semibold">Prediction Championship Leaderboard</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Rank
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Total Points
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Accuracy
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Predictions
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Perfect Predictions
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    <!-- User 1 -->
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-mary-badge class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    1
                                </x-mary-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                        <x-mary-icon name="o-user" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="font-semibold">F1Expert</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Member since 2020</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h3 class="font-semibold text-green-600 dark:text-green-400">1,245</h3>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-mary-badge class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    87.3%
                                </x-mary-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>156</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>23</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <x-mary-button variant="outline" size="sm" icon="o-eye">
                                    View
                                </x-mary-button>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button">
                                        <x-mary-button variant="ghost" size="sm" icon="o-ellipsis-vertical" />
                                    </div>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-user" class="w-4 h-4" /><span>Profile</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-chart-bar" class="w-4 h-4" /><span>Statistics</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-star" class="w-4 h-4" /><span>Follow</span></a></li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- User 2 -->
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-mary-badge class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    2
                                </x-mary-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                        <x-mary-icon name="o-user" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="font-semibold">RacingFan2023</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Member since 2023</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h3 class="font-semibold text-green-600 dark:text-green-400">1,187</h3>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-mary-badge class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    84.1%
                                </x-mary-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>142</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>19</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <x-mary-button variant="outline" size="sm" icon="o-eye">
                                    View
                                </x-mary-button>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button">
                                        <x-mary-button variant="ghost" size="sm" icon="o-ellipsis-vertical" />
                                    </div>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-user" class="w-4 h-4" /><span>Profile</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-chart-bar" class="w-4 h-4" /><span>Statistics</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-star" class="w-4 h-4" /><span>Follow</span></a></li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- User 3 -->
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-mary-badge class="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    3
                                </x-mary-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center">
                                        <x-mary-icon name="o-user" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="font-semibold">PredictorPro</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Member since 2021</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h3 class="font-semibold text-green-600 dark:text-green-400">1,156</h3>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-mary-badge class="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    82.7%
                                </x-mary-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>168</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>21</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <x-mary-button variant="outline" size="sm" icon="o-eye">
                                    View
                                </x-mary-button>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button">
                                        <x-mary-button variant="ghost" size="sm" icon="o-ellipsis-vertical" />
                                    </div>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-user" class="w-4 h-4" /><span>Profile</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-chart-bar" class="w-4 h-4" /><span>Statistics</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-star" class="w-4 h-4" /><span>Follow</span></a></li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-mary-card>

    <!-- Pagination -->
    <div class="mt-8 flex items-center justify-between">
        <p class="text-zinc-600 dark:text-zinc-400">
            Showing 1-3 of 50 users
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
