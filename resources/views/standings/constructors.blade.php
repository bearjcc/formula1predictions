<x-layouts.layout :title="$year . ' Constructor Standings'" :headerSubtitle="'Current constructor championship standings for the ' . $year . ' Formula 1 season'">
    <x-standings-tabs :year="$year" />

    <x-mary-card class="overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-heading-3">Constructor Championship Standings</h3>
        </div>

        {{-- Mobile: tappable card rows (hidden on sm+) --}}
        <ul class="sm:hidden divide-y divide-zinc-200 dark:divide-zinc-700">
            @forelse($teamRows as $row)
                <li>
                    @if(!empty($row['team_slug'] ?? null))
                        <a href="{{ route('constructor', ['slug' => $row['team_slug']]) }}"
                           class="flex items-center gap-3 px-4 py-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 active:bg-zinc-100 dark:active:bg-zinc-700 transition-colors">
                    @else
                        <div class="flex items-center gap-3 px-4 py-3.5">
                    @endif

                        {{-- Position + trophy --}}
                        <div class="flex-shrink-0 w-7 flex flex-col items-center gap-0.5">
                            @if($seasonStarted)
                                <span class="text-sm font-bold tabular-nums text-zinc-800 dark:text-zinc-200">{{ $row['position'] }}</span>
                                @if($seasonEnded && in_array($row['position'], [1, 2, 3], true))
                                    <x-mary-icon name="o-trophy" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400" />
                                @endif
                            @else
                                <span class="text-sm text-zinc-400">—</span>
                            @endif
                        </div>

                        {{-- Constructor name + drivers (with constructor colour bar) --}}
                        <div class="flex-1 min-w-0">
                            @php $teamDisplayName = $row['team_display_name'] ?? $row['team_name']; @endphp
                            <x-constructor-bar :teamName="$row['team_name']">
                                <p class="font-semibold text-zinc-900 dark:text-zinc-100 truncate leading-tight">{{ $teamDisplayName }}</p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    @if(!empty($row['driver_names']))
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ implode(' · ', $row['driver_names']) }}</span>
                                    @endif
                                    @if($row['country_flag_url'] ?? null)
                                        <img src="{{ $row['country_flag_url'] }}" alt="{{ $row['nationality'] ?? '' }}" class="h-3 w-auto max-w-5 object-contain flex-shrink-0" loading="lazy" />
                                    @endif
                                </div>
                            </x-constructor-bar>
                        </div>

                        {{-- Points + wins/podiums --}}
                        <div class="flex-shrink-0 text-right">
                            <p class="text-base font-bold text-green-600 dark:text-green-400 tabular-nums leading-tight">{{ number_format($row['points'], 0) }}</p>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5 tabular-nums">{{ $row['wins'] }}W&nbsp;·&nbsp;{{ $row['podiums'] }}P</p>
                        </div>

                        @if(!empty($row['team_slug'] ?? null))
                            <x-mary-icon name="o-chevron-right" class="flex-shrink-0 w-4 h-4 text-zinc-300 dark:text-zinc-600" />
                        @endif

                    @if(!empty($row['team_slug'] ?? null))
                        </a>
                    @else
                        </div>
                    @endif
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('No constructor standings data for this season yet. Standings are updated from our database after each race.') }}
                </li>
            @endforelse
        </ul>

        {{-- Desktop: full table (hidden on mobile) --}}
        <div class="hidden sm:block w-full max-w-full min-w-0 overflow-x-auto [-webkit-overflow-scrolling:touch]">
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
