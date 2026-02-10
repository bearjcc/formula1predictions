<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $team->team_name }}</h1>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Complete team information and statistics for {{ $team->team_name }}
                </p>
            </div>
        </div>
    </div>

    <!-- Team Overview Card -->
    <x-mary-card class="mb-8">
        <div class="flex items-start space-x-6">
            <!-- Team Logo -->
            <div class="flex-shrink-0">
                <div class="w-24 h-24 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-users" class="w-12 h-12 text-red-600 dark:text-red-400" />
                </div>
            </div>

            <!-- Team Info -->
            <div class="flex-1">
                <h2 class="text-2xl font-bold mb-2">{{ $team->team_name }}</h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    @if($team->base_location)Based in {{ $team->base_location }}.@endif
                    @if($team->nationality) {{ $team->nationality }}.@endif
                    @if($team->founded) Founded in {{ $team->founded }}.@endif
                </p>

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $team->world_championships ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $team->race_wins ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $team->podiums ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Podiums</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $team->pole_positions ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Pole Positions</p>
                    </div>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Team Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Current Drivers -->
        <x-mary-card>
            <h3 class="text-xl font-bold mb-4">Current Drivers</h3>
            <div class="space-y-4">
                @forelse($team->drivers as $driver)
                    <div class="flex items-center space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                            <x-mary-icon name="o-user" class="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold">{{ $driver->full_name }}</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                @if($driver->driver_number)Driver #{{ $driver->driver_number }}@endif
                            </p>
                        </div>
                        <a href="{{ route('driver', $driver->slug) }}">
                            <x-mary-button variant="outline" size="sm" icon="o-eye">
                                View
                            </x-mary-button>
                        </a>
                    </div>
                @empty
                    <p class="text-zinc-500 dark:text-zinc-400">No active drivers found.</p>
                @endforelse
            </div>
        </x-mary-card>

        <!-- Team Information -->
        <x-mary-card>
            <h3 class="text-xl font-bold mb-4">Team Information</h3>
            <div class="space-y-4">
                @if($team->team_principal)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Team Principal</p>
                        <p class="font-medium">{{ $team->team_principal }}</p>
                    </div>
                @endif
                @if($team->technical_director)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Technical Director</p>
                        <p class="font-medium">{{ $team->technical_director }}</p>
                    </div>
                @endif
                @if($team->chassis)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Chassis</p>
                        <p class="font-medium">{{ $team->chassis }}</p>
                    </div>
                @endif
                @if($team->power_unit)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Power Unit</p>
                        <p class="font-medium">{{ $team->power_unit }}</p>
                    </div>
                @endif
                @if($team->nationality)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Nationality</p>
                        <p class="font-medium">{{ $team->nationality }}</p>
                    </div>
                @endif
                @if($team->founded)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Founded</p>
                        <p class="font-medium">{{ $team->founded }}</p>
                    </div>
                @endif
            </div>
        </x-mary-card>
    </div>

    <!-- Career Statistics -->
    <x-mary-card>
        <h3 class="text-xl font-bold mb-4">Career Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $team->world_championships ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Championships</p>
            </div>
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $team->race_wins ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Wins</p>
            </div>
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $team->podiums ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Podiums</p>
            </div>
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $team->pole_positions ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Pole Positions</p>
            </div>
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $team->fastest_laps ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Fastest Laps</p>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>
