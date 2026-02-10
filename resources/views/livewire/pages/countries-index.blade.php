<x-layouts.layout>
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 mb-2">F1 Countries</h1>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Explore Formula 1 history and statistics by country
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-mary-button variant="outline" size="sm" icon="o-map">
                    Map View
                </x-mary-button>
                <x-mary-button variant="primary" size="sm" icon="o-plus">
                    Compare Countries
                </x-mary-button>
            </div>
        </div>
    </div>

    <x-mary-card class="mb-8">
        <h3 class="text-heading-3 mb-4">Filters</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Region</label>
                <x-mary-select disabled>
                    <option value="">All Regions</option>
                </x-mary-select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Championships</label>
                <x-mary-select wire:model.live="championships">
                    <option value="all">All Countries</option>
                    <option value="1-5">1-5 Championships</option>
                    <option value="6-10">6-10 Championships</option>
                    <option value="10+">10+ Championships</option>
                </x-mary-select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                <x-mary-select wire:model.live="status">
                    <option value="all">All Countries</option>
                    <option value="active">Active in F1</option>
                    <option value="historic">Historic</option>
                </x-mary-select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
                <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search countries..." icon="o-magnifying-glass" />
            </div>
        </div>
    </x-mary-card>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($countries as $country)
            <x-mary-card class="overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center shrink-0">
                            @if($country->flag_url ?? null)
                                <img src="{{ $country->flag_url }}" alt="" class="w-8 h-8 object-contain" />
                            @else
                                <x-mary-icon name="o-flag" class="w-6 h-6 text-red-600 dark:text-red-400" />
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xl font-bold truncate">{{ $country->name }}</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $country->code }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center">
                            <h4 class="text-lg font-bold text-green-600 dark:text-green-400">{{ $country->world_championships_won ?? 0 }}</h4>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400">Championships</p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-lg font-bold text-green-600 dark:text-green-400">{{ $country->f1_races_hosted ?? 0 }}</h4>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400">Races Hosted</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <a href="{{ route('country', ['slug' => $country->slug]) }}">
                            <x-mary-button variant="outline" size="sm" icon="o-eye">
                                View Details
                            </x-mary-button>
                        </a>
                    </div>
                </div>
            </x-mary-card>
        @empty
            <div class="md:col-span-2 lg:col-span-3 text-center py-12 text-zinc-600 dark:text-zinc-400">
                No countries match your filters. Try adjusting search or filter criteria.
            </div>
        @endforelse
    </div>

    @if($countries->hasPages())
        <div class="flex flex-wrap items-center justify-between gap-4">
            <p class="text-zinc-600 dark:text-zinc-400">
                Showing {{ $countries->firstItem() }}-{{ $countries->lastItem() }} of {{ $countries->total() }} countries
            </p>
            <div class="flex items-center gap-2">
                {{ $countries->withQueryString()->links() }}
            </div>
        </div>
    @elseif($countries->total() > 0)
        <p class="text-zinc-600 dark:text-zinc-400">
            Showing {{ $countries->total() }} {{ str('country')->plural($countries->total()) }}
        </p>
    @endif
</x-layouts.layout>
