<x-layouts.layout :title="$year . ' Constructor Standings'" :headerSubtitle="'Current constructor championship standings for the ' . $year . ' Formula 1 season'">
    <x-standings-tabs :year="$year" />

    <x-mary-card class="overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-heading-3">Constructor Championship Standings</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Position
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Constructor
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
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($teamRows as $row)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($row['position'] === 1)
                                        <x-mary-badge variant="filled" class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            {{ $row['position'] }}
                                        </x-mary-badge>
                                    @elseif($row['position'] === 2)
                                        <x-mary-badge variant="filled" class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            {{ $row['position'] }}
                                        </x-mary-badge>
                                    @elseif($row['position'] === 3)
                                        <x-mary-badge variant="filled" class="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                            {{ $row['position'] }}
                                        </x-mary-badge>
                                    @else
                                        <x-mary-badge variant="outline">{{ $row['position'] }}</x-mary-badge>
                                    @endif
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
                                        <h4 class="font-semibold">{{ $row['team_name'] }}</h4>
                                        @if($row['nationality'] ?? null)
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $row['nationality'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!empty($row['driver_names']))
                                    <div class="flex flex-col">
                                        @foreach($row['driver_names'] as $name)
                                            <p class="text-sm">{{ $name }}</p>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-500">—</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($row['points'], 0) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p>{{ $row['wins'] }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p>{{ $row['podiums'] }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No constructor standings data for this season yet. Standings are updated from our database after each race.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-mary-card>
</x-layouts.layout>
