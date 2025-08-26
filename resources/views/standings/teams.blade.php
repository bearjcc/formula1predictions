<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $year }} Team Standings</h1>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Current constructor championship standings for the {{ $year }} Formula 1 season
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                    Export
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-plus">
                    Compare Teams
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <x-mary-card class="mb-8">
        <h3 class="text-xl font-bold mb-4">Filters</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Engine</label>
                <x-mary-select>
                    <option value="">All Engines</option>
                    <option value="honda">Honda RBPT</option>
                    <option value="mercedes">Mercedes</option>
                    <option value="ferrari">Ferrari</option>
                    <option value="renault">Renault</option>
                </x-mary-select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Country</label>
                <x-mary-select>
                    <option value="">All Countries</option>
                    <option value="uk">United Kingdom</option>
                    <option value="italy">Italy</option>
                    <option value="austria">Austria</option>
                    <option value="france">France</option>
                </x-mary-select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                <x-mary-select>
                    <option value="">All Teams</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </x-mary-select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
                <x-mary-input placeholder="Search teams..." icon="o-magnifying-glass" />
            </div>
        </div>
    </x-mary-card>

    <!-- Team Standings Table -->
    <x-mary-card class="overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-xl font-bold">Constructor Championship Standings</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Position
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Team
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Drivers
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Points
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Wins
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Podiums
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    <!-- Team 1 -->
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-mary-badge variant="filled" class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    1
                                </x-mary-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-red-100 dark:bg-red-900 flex items-center justify-center">
                                        <x-mary-icon name="o-users" class="w-6 h-6 text-red-600 dark:text-red-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">Red Bull Racing</h4>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Austria</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <p class="text-sm">Max Verstappen</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Sergio Pérez</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h4 class="text-lg font-bold text-green-600 dark:text-green-400">860</h4>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>21</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>30</p>
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
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-users" class="w-4 h-4" /><span>Profile</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-chart-bar" class="w-4 h-4" /><span>Statistics</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-star" class="w-4 h-4" /><span>Favorite</span></a></li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Team 2 -->
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-mary-badge variant="filled" class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    2
                                </x-mary-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-silver-100 dark:bg-silver-900 flex items-center justify-center">
                                        <x-mary-icon name="o-users" class="w-6 h-6 text-silver-600 dark:text-silver-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">Mercedes</h4>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">United Kingdom</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <p class="text-sm">Lewis Hamilton</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">George Russell</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h4 class="text-lg font-bold text-green-600 dark:text-green-400">409</h4>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>0</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>8</p>
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
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-users" class="w-4 h-4" /><span>Profile</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-chart-bar" class="w-4 h-4" /><span>Statistics</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-star" class="w-4 h-4" /><span>Favorite</span></a></li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Team 3 -->
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-mary-badge variant="filled" class="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    3
                                </x-mary-badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-orange-100 dark:bg-orange-900 flex items-center justify-center">
                                        <x-mary-icon name="o-users" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">Ferrari</h4>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Italy</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <p class="text-sm">Charles Leclerc</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Carlos Sainz</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <h4 class="text-lg font-bold text-green-600 dark:text-green-400">406</h4>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>1</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p>9</p>
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
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-users" class="w-4 h-4" /><span>Profile</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-chart-bar" class="w-4 h-4" /><span>Statistics</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-star" class="w-4 h-4" /><span>Favorite</span></a></li>
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
            Showing 1-3 of 10 teams
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
