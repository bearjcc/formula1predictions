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
            <div class="mt-4">
                <x-mary-button variant="outline" size="sm" wire:click="refreshRaces" icon="o-arrow-path">
                    Try Again
                </x-mary-button>
            </div>
        </div>
    @else
        <!-- Filters Section -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 mb-8">
            <h3 class="text-lg font-semibold mb-4">Filters</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                    <x-mary-select wire:model.live="statusFilter">
                        <option value="">All Races</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </x-mary-select>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
                    <x-mary-input 
                        wire:model.live.debounce.300ms="searchQuery" 
                        placeholder="Search races, circuits, or countries..." 
                        icon="o-magnifying-glass" 
                    />
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">&nbsp;</label>
                    <x-mary-button 
                        variant="outline" 
                        wire:click="refreshRaces" 
                        icon="o-arrow-path"
                        class="w-full"
                    >
                        Refresh Data
                    </x-mary-button>
                </div>
            </div>
        </div>

        <!-- Races List -->
        @if(count($this->filteredRaces) > 0)
            <div class="space-y-6">
                @foreach($this->filteredRaces as $race)
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-3">
                                    <x-mary-badge 
                                        variant="{{ $this->getStatusBadgeVariant($race['status']) }}" 
                                        class="{{ $this->getStatusBadgeClass($race['status']) }}"
                                    >
                                        {{ ucfirst($race['status']) }}
                                    </x-mary-badge>
                                    <h3 class="text-lg font-semibold">{{ $race['raceName'] }}</h3>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="flex items-center space-x-2">
                                        <x-mary-icon name="o-map-pin" class="w-4 h-4 text-zinc-500" />
                                        <span>{{ $race['circuit']['circuitName'] }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <x-mary-icon name="o-calendar" class="w-4 h-4 text-zinc-500" />
                                        <span>{{ \Carbon\Carbon::parse($race['date'])->format('M j, Y') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <x-mary-icon name="o-flag" class="w-4 h-4 text-zinc-500" />
                                        <span>{{ $race['circuit']['country'] }}</span>
                                    </div>
                                </div>
                                
                                @if(isset($race['circuit']['circuitLength']))
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div class="flex items-center space-x-2">
                                            <x-mary-icon name="o-map" class="w-4 h-4 text-zinc-500" />
                                            <span>Length: {{ $race['circuit']['circuitLength'] }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <x-mary-icon name="o-clock" class="w-4 h-4 text-zinc-500" />
                                            <span>Time: {{ \Carbon\Carbon::parse($race['time'])->format('H:i') }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <x-mary-icon name="o-trophy" class="w-4 h-4 text-zinc-500" />
                                            <span>Round {{ $race['round'] }}</span>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="flex items-center space-x-4">
                                    <x-mary-button variant="outline" size="sm" icon="o-eye">
                                        View Details
                                    </x-mary-button>
                                    @if($race['status'] === 'completed' && !empty($race['results']))
                                        <x-mary-button variant="outline" size="sm" icon="o-chart-bar">
                                            Results ({{ count($race['results']) }} drivers)
                                        </x-mary-button>
                                    @endif
                                    @if($race['status'] === 'upcoming')
                                        <x-mary-button 
                                            variant="primary" 
                                            size="sm" 
                                            icon="o-plus"
                                            wire:click="makePrediction({{ $race['round'] }})"
                                        >
                                            Make Prediction
                                        </x-mary-button>
                                    @endif
                                    <x-mary-button variant="outline" size="sm" icon="o-users">
                                        Predictions
                                    </x-mary-button>
                                </div>
                            </div>
                            
                            <div class="flex flex-col items-end space-y-2">
                                <x-mary-button variant="ghost" size="sm" icon="o-star">
                                    Favorite
                                </x-mary-button>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button">
                                        <x-mary-button variant="ghost" size="sm" icon="o-ellipsis-vertical" />
                                    </div>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-pencil" class="w-4 h-4" /><span>Edit</span></a></li>
                                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-document-duplicate" class="w-4 h-4" /><span>Duplicate</span></a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="flex items-center space-x-2 text-red-600"><x-mary-icon name="o-trash" class="w-4 h-4" /><span>Delete</span></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Results Summary -->
            <div class="mt-8 flex items-center justify-between">
                <p class="text-zinc-600 dark:text-zinc-400">
                    Showing {{ count($this->filteredRaces) }} of {{ count($races) }} races
                </p>
            </div>
        @else
            <!-- No Results -->
            <div class="text-center py-12">
                <x-mary-icon name="o-magnifying-glass" class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-zinc-600 dark:text-zinc-400 mb-2">No races found</h3>
                <p class="text-zinc-500 dark:text-zinc-500">
                    @if(!empty($statusFilter) || !empty($searchQuery))
                        Try adjusting your filters or search terms.
                    @else
                        No races available for {{ $year }}.
                    @endif
                </p>
            </div>
        @endif
    @endif
</div>
