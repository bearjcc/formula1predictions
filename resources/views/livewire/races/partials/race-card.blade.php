@php
    $status = $race['status'] ?? 'upcoming';
    $circuit = is_array($race['circuit'] ?? null) ? $race['circuit'] : [];
    $badgeClass = match ($status) {
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'upcoming' => 'border-blue-200 text-blue-700 dark:border-blue-700 dark:text-blue-300',
        'ongoing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        default => 'border-zinc-200 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300',
    };

    // Prediction state
    $predictionId = $race['user_prediction_id'] ?? null;
    $predictionStatus = $race['user_prediction_status'] ?? null;
    $predictionScore = $race['user_prediction_score'] ?? null;
    $predictionEditable = $race['user_prediction_editable'] ?? false;
    $hasPrediction = $predictionId !== null;
    $predictionsOpen = !empty($race['predictions_open']);

    // Sprint prediction state
    $sprintPredictionId = $race['user_sprint_prediction_id'] ?? null;
    $hasSprintPrediction = $race['user_has_sprint_prediction'] ?? false;
    $sprintEditable = $race['user_sprint_prediction_editable'] ?? false;

    // Determine the prediction status badge text + style
    $predBadgeText = match ($predictionStatus) {
        'scored' => null, // show score instead
        'locked' => 'Locked in',
        'submitted' => 'Submitted',
        'draft' => 'Draft saved',
        default => null,
    };
    $predBadgeClass = match ($predictionStatus) {
        'scored' => 'bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-300',
        'locked' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300',
        'submitted', 'draft' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300',
        default => '',
    };
@endphp
<div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center space-x-3 mb-3">
                <x-mary-badge variant="outline" class="{{ $badgeClass }}">
                    {{ $race['round'] ?? '?' }}
                </x-mary-badge>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $race['raceName'] ?? 'Race' }}</h3>

                {{-- Prediction status badge --}}
                @if($hasPrediction)
                    @if($predictionStatus === 'scored' && $predictionScore !== null)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-300">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $predictionScore }} pts
                        </span>
                    @elseif($predBadgeText)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $predBadgeClass }}">
                            @if($predictionStatus === 'locked')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                            {{ $predBadgeText }}
                        </span>
                    @endif
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="o-map-pin" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                    <span class="text-zinc-700 dark:text-zinc-300">{{ $circuit['circuitName'] ?? 'TBA circuit' }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="o-calendar" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                    <span class="text-zinc-700 dark:text-zinc-300">
                        @if(!empty($race['date']))
                            @php
                                try {
                                    $dateStr = \Carbon\Carbon::parse($race['date'])->format('M j, Y');
                                } catch (\Throwable) {
                                    $dateStr = 'TBA';
                                }
                            @endphp
                            {{ $dateStr }}
                        @else
                            TBA
                        @endif
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    @php
                        $countryName = $circuit['country'] ?? null;
                        $countryCode = $countryName ? (config('country_flags.'.$countryName) ?? null) : null;
                    @endphp
                    @if($countryCode)
                        <span class="fi fi-{{ $countryCode }} fis inline-block w-4 h-4 rounded-sm align-middle bg-cover" aria-hidden="true"></span>
                    @else
                        <x-mary-icon name="o-flag" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                    @endif
                    <span class="text-zinc-700 dark:text-zinc-300">{{ $countryName ?? 'TBA' }}</span>
                </div>
            </div>

            @if(!empty($circuit['circuitLength']))
                @php
                    $lengthKm = (float) $circuit['circuitLength'];
                    if ($lengthKm > 100) {
                        $lengthKm = $lengthKm / 1000;
                    }
                    $lengthFormatted = number_format($lengthKm, 1) . ' km';
                    $lapsFormatted = isset($race['laps']) && $race['laps'] !== null && $race['laps'] !== '' ? (int) $race['laps'] . ' laps' : null;
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="flex items-center space-x-2">
                        <x-mary-icon name="o-map" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-zinc-700 dark:text-zinc-300">Length: {{ $lengthFormatted }}@if($lapsFormatted), {{ $lapsFormatted }}@endif</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-mary-icon name="o-clock" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-zinc-700 dark:text-zinc-300">
                            @if(!empty($race['time']))
                                @php
                                    try {
                                        $timeStr = \Carbon\Carbon::parse($race['time'])->format('H:i');
                                    } catch (\Throwable) {
                                        $timeStr = 'TBA';
                                    }
                                @endphp
                                Time: {{ $timeStr }}
                            @else
                                Time: TBA
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-mary-icon name="o-trophy" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-zinc-700 dark:text-zinc-300">Round {{ $race['round'] ?? '?' }}</span>
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-2">
                <x-mary-button variant="outline" size="sm" icon="o-eye" wire:click="viewDetails({{ (int) ($race['round'] ?? 0) }})">
                    View Details
                </x-mary-button>
                @if(($race['status'] ?? '') === 'completed' && !empty($race['results']))
                    <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                        Results ({{ count($race['results']) }} drivers)
                    </x-mary-button>
                @endif

                {{-- Race prediction CTA --}}
                @if($hasPrediction && $predictionEditable)
                    <x-mary-button
                        variant="primary"
                        size="sm"
                        icon="o-pencil-square"
                        wire:click="editPrediction({{ $predictionId }})"
                    >
                        Edit Prediction
                    </x-mary-button>
                @elseif(!$hasPrediction && $predictionsOpen)
                    <x-mary-button
                        variant="primary"
                        size="sm"
                        icon="o-plus"
                        wire:click="makePrediction({{ (int) ($race['round'] ?? 0) }})"
                    >
                        Make Prediction
                    </x-mary-button>
                @elseif($hasPrediction && !$predictionEditable && $predictionStatus !== 'scored')
                    {{-- Locked but not yet scored --}}
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-sm font-medium text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Prediction locked
                    </span>
                @endif

                {{-- Sprint prediction CTA (shown alongside race CTA when applicable) --}}
                @if($hasSprintPrediction && $sprintEditable)
                    <x-mary-button
                        variant="outline"
                        size="sm"
                        icon="o-pencil-square"
                        wire:click="editPrediction({{ $sprintPredictionId }})"
                    >
                        Edit Sprint
                    </x-mary-button>
                @elseif(!$hasSprintPrediction && $predictionsOpen && isset($race['has_sprint']) && $race['has_sprint'])
                    <x-mary-button
                        variant="outline"
                        size="sm"
                        icon="o-plus"
                        wire:click="makePrediction({{ (int) ($race['round'] ?? 0) }})"
                    >
                        Make Sprint Prediction
                    </x-mary-button>
                @endif
            </div>
        </div>
    </div>
</div>
