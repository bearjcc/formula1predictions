@php
    use Carbon\Carbon;
    $date = Carbon::parse($monthKey . '-01');
    $monthName = $date->format('F Y');
    $daysInMonth = $date->daysInMonth;
    $firstDayOfWeek = (int) $date->copy()->startOfMonth()->format('w'); // 0 = Sunday
    $racesByDay = [];
    foreach ($races as $race) {
        $start = isset($race['weekend_start']) ? Carbon::parse($race['weekend_start']) : Carbon::parse($race['date']);
        $end = isset($race['weekend_end']) ? Carbon::parse($race['weekend_end']) : Carbon::parse($race['date']);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cellDate = Carbon::createFromDate($date->year, $date->month, $d)->format('Y-m-d');
            if ($cellDate >= $start->format('Y-m-d') && $cellDate <= $end->format('Y-m-d')) {
                if (! isset($racesByDay[$d])) {
                    $racesByDay[$d] = [];
                }
                $racesByDay[$d][] = $race;
            }
        }
    }
    $weeks = [];
    $week = array_fill(0, 7, null);
    $dayCount = 0;
    for ($i = 0; $i < $firstDayOfWeek; $i++) {
        $week[$i] = ['day' => null, 'races' => []];
    }
    $dayCount = $firstDayOfWeek;
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $dayRaces = $racesByDay[$d] ?? [];
        $week[$dayCount % 7] = ['day' => $d, 'races' => $dayRaces];
        $dayCount++;
        if ($dayCount % 7 === 0) {
            $weeks[] = $week;
            $week = array_fill(0, 7, null);
        }
    }
    if ($dayCount % 7 !== 0) {
        $weeks[] = $week;
    }
    $weekdayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
@endphp
<div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
    <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 font-semibold text-zinc-900 dark:text-zinc-100">
        {{ $monthName }}
    </div>
    <div class="p-3">
        <table class="w-full text-sm" role="grid" aria-label="Calendar for {{ $monthName }}">
            <thead>
                <tr>
                    @foreach($weekdayNames as $name)
                        <th class="text-zinc-500 dark:text-zinc-400 font-medium py-1 text-center w-[14%]" scope="col">{{ $name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($weeks as $weekRow)
                    <tr>
                        @foreach($weekRow as $cell)
                            @if($cell === null || (is_array($cell) && $cell['day'] === null))
                                <td class="p-0.5 text-center"></td>
                            @else
                                @php
                                    $day = is_array($cell) ? $cell['day'] : null;
                                    $dayRaces = is_array($cell) ? ($cell['races'] ?? []) : [];
                                    $race = ! empty($dayRaces) ? $dayRaces[0] : null;
                                @endphp
                                <td class="p-0.5 text-center align-top">
                                    @if($race)
                                        @php
                                            $detailUrl = ! empty($race['slug'])
                                                ? route('race.detail', ['slug' => $race['slug']])
                                                : null;
                                        @endphp
                                        @if($detailUrl)
                                            <a href="{{ $detailUrl }}" wire:navigate
                                               class="inline-block w-8 h-8 leading-8 rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-900/50 font-medium"
                                               title="{{ $race['raceName'] ?? 'Race' }}">
                                                {{ $day }}
                                            </a>
                                        @else
                                            <span class="inline-block w-8 h-8 leading-8 rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 font-medium" title="{{ $race['raceName'] ?? 'Race' }}">{{ $day }}</span>
                                        @endif
                                    @else
                                        <span class="inline-block w-8 h-8 leading-8 text-zinc-700 dark:text-zinc-300">{{ $day }}</span>
                                    @endif
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
