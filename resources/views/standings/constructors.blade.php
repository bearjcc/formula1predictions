<x-layouts.layout :title="$year . ' Constructor Standings'" :headerSubtitle="'Current constructor championship standings for the ' . $year . ' Formula 1 season'">
    <x-standings-tabs :year="$year" />

    <x-mary-card class="overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-heading-3">Constructor Championship Standings</h3>
        </div>

        <div class="w-full max-w-full min-w-0 overflow-x-auto [-webkit-overflow-scrolling:touch]">
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
                            Country
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
                                <x-constructor-bar :teamName="$row['team_name']">
                                    @php
                                        $teamDisplayName = $row['team_display_name'] ?? $row['team_name'];
                                    @endphp
                                    @if(!empty($row['team_slug'] ?? null))
                                        <a
                                            href="{{ route('constructor', ['slug' => $row['team_slug']]) }}"
                                            class="inline-flex items-center gap-1 text-auto-primary hover:underline"
                                        >
                                            <h4 class="font-semibold">{{ $teamDisplayName }}</h4>
                                        </a>
                                    @else
                                        <h4 class="font-semibold">{{ $teamDisplayName }}</h4>
                                    @endif
                                </x-constructor-bar>
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
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No constructor standings data for this season yet. Standings are updated from our database after each race.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-mary-card>
</x-layouts.layout>
