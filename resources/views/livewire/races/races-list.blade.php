<div>
    <!-- Loading State -->
    @if($loading)
        <div class="flex items-center justify-center py-12">
            <div class="text-center">
                <x-mary-icon name="o-arrow-path" class="w-8 h-8 text-zinc-400 animate-spin mx-auto mb-4" />
                <p class="text-zinc-600 dark:text-zinc-400">Loading races...</p>
            </div>
        </div>
    @elseif($error)
        <!-- Error State -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 mb-8">
            <div class="flex items-center space-x-3">
                <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6 text-red-500" />
                <div>
                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">Error Loading Races</h3>
                    <p class="text-red-600 dark:text-red-300">{{ $error }}</p>
                </div>
            </div>
            @if(auth()->user()?->hasRole('admin'))
                <div class="mt-4">
                    <x-mary-button variant="outline" size="sm" wire:click="refreshRaces" icon="o-arrow-path">
                        Try Again
                    </x-mary-button>
                </div>
            @else
                <p class="mt-4 text-sm text-red-700 dark:text-red-300">
                    Data is managed automatically. If this keeps happening, please contact an admin.
                </p>
            @endif
        </div>
    @else
        @php($grouped = $this->groupedRaces)
        @if(count($this->races) > 0)
            @php($preseasonInfo = $this->preseasonInfo)
            @if($preseasonInfo['deadline'])
                <div class="mb-4 p-4 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Preseason prediction</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            Full-season predictions (constructors, teammate battles, red flags, safety cars). Due at the same time as the first race.
                            @if($preseasonInfo['first_race_name'])
                                Closes when {{ $preseasonInfo['first_race_name'] }} predictions close.
                            @endif
                            @if($preseasonInfo['deadline'])
                                <span class="block mt-1 text-amber-600 dark:text-amber-400">{{ $preseasonInfo['deadline']->format('M j, Y g:i A T') }}</span>
                            @endif
                        </p>
                    </div>
                    <x-mary-button variant="primary" size="sm" icon="o-pencil-square" link="{{ route('predict.preseason', ['year' => $year]) }}" wire:navigate>
                        Make preseason prediction
                    </x-mary-button>
                </div>
            @endif
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2">
                    <x-mary-button variant="{{ $viewMode === 'list' ? 'primary' : 'outline' }}" size="sm" wire:click="showList" icon="o-list-bullet">
                        List View
                    </x-mary-button>
                    <x-mary-button variant="{{ $viewMode === 'calendar' ? 'primary' : 'outline' }}" size="sm" wire:click="showCalendar" icon="o-calendar">
                        Calendar View
                    </x-mary-button>
                </div>
                @if(auth()->user()?->hasRole('admin'))
                    <x-mary-button variant="outline" size="sm" wire:click="refreshRaces" icon="o-arrow-path">
                        Refresh Data
                    </x-mary-button>
                @endif
            </div>

            @if($viewMode === 'calendar')
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($this->racesByMonth as $monthKey => $monthRaces)
                        @include('livewire.races.partials.calendar-month', ['monthKey' => $monthKey, 'races' => $monthRaces, 'year' => $year])
                    @endforeach
                </div>
            @else
            <div class="space-y-4">
                @if(!empty($grouped['next']))
                    <details class="group bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700" open>
                        <summary class="px-6 py-4 cursor-pointer list-none font-semibold text-lg flex items-center gap-2">
                            <x-mary-icon name="o-chevron-right" class="w-5 h-5 transition-transform group-open:rotate-90" />
                            Next race
                        </summary>
                        <div class="px-6 pb-6">
                            @include('livewire.races.partials.race-card', ['race' => $grouped['next']])
                        </div>
                    </details>
                @endif

                @if(!empty($grouped['future']))
                    <details class="group bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700" open>
                        <summary class="px-6 py-4 cursor-pointer list-none font-semibold text-lg flex items-center gap-2">
                            <x-mary-icon name="o-chevron-right" class="w-5 h-5 transition-transform group-open:rotate-90" />
                            Future races ({{ count($grouped['future']) }})
                        </summary>
                        <div class="px-6 pb-6 space-y-4">
                            @foreach($grouped['future'] as $race)
                                @include('livewire.races.partials.race-card', ['race' => $race])
                            @endforeach
                        </div>
                    </details>
                @endif

                @if(!empty($grouped['past']))
                    <details class="group bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <summary class="px-6 py-4 cursor-pointer list-none font-semibold text-lg flex items-center gap-2">
                            <x-mary-icon name="o-chevron-right" class="w-5 h-5 transition-transform group-open:rotate-90" />
                            Past races ({{ count($grouped['past']) }})
                        </summary>
                        <div class="px-6 pb-6 space-y-4">
                            @foreach($grouped['past'] as $race)
                                @include('livewire.races.partials.race-card', ['race' => $race])
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
            @endif

            @if(empty($grouped['next']) && empty($grouped['future']) && empty($grouped['past']))
                <div class="text-center py-12">
                    <p class="text-zinc-500 dark:text-zinc-400">No races available for {{ $year }}.</p>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <x-mary-icon name="o-calendar-days" class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-zinc-600 dark:text-zinc-400 mb-2">No races found</h3>
                <p class="text-zinc-500 dark:text-zinc-500">No races available for {{ $year }}.</p>
                @if(auth()->user()?->hasRole('admin'))
                    <div class="mt-4">
                        <x-mary-button variant="outline" size="sm" wire:click="refreshRaces" icon="o-arrow-path">Try again</x-mary-button>
                    </div>
                @else
                    <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">
                        Race data will update automatically once it’s available.
                    </p>
                @endif
            </div>
        @endif
    @endif
</div>
