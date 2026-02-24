<x-layouts.layout :title="$year . ' Driver Standings'" :headerSubtitle="'Current driver championship standings for the ' . $year . ' Formula 1 season'">
    <x-standings-tabs :year="$year" />

    <!-- Driver Standings Table -->
    <x-mary-card class="overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-heading-3">Driver Championship Standings</h2>
        </div>

        <div class="w-full max-w-full min-w-0 overflow-x-auto [-webkit-overflow-scrolling:touch]">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Position
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Points
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Driver
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Constructor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Country
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
                    @forelse($driverRows as $row)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    @if($seasonStarted)
                                        <x-mary-badge variant="outline" :value="$row['position']" />
                                        @if($seasonEnded && in_array($row['position'], [1, 2, 3], true))
                                            <x-mary-icon name="o-trophy" class="w-5 h-5 text-amber-500 dark:text-amber-400" title="{{ __('Position clinched') }}" />
                                        @endif
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400">0</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-green-600 dark:text-green-400">{{ number_format($row['points'], 0) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-constructor-bar :teamName="$row['team_name'] ?? null">
                                    <h3 class="font-semibold">{{ $row['driver_name'] }}</h3>
                                </x-constructor-bar>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p>{{ $row['team_display_name'] ?? $row['team_name'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($row['nationality'] ?? null)
                                    <div class="flex items-center gap-2">
                                        @if($row['country_flag_url'] ?? null)
                                            <img src="{{ $row['country_flag_url'] }}" alt="" class="h-6 w-auto max-w-10 object-contain" loading="lazy" />
                                        @endif
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $row['nationality'] }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-500">—</span>
                                @endif
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
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No driver standings data for this season yet. Standings are updated from our database after each race.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-mary-card>
</x-layouts.layout>
