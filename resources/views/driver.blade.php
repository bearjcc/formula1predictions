<x-layouts.layout :title="$driver->full_name" :headerSubtitle="'Complete driver information and statistics for ' . $driver->full_name">
    <!-- Driver Overview Card -->
    <x-mary-card class="mb-8">
        <div class="flex items-start space-x-6">
            <!-- Driver Photo -->
            <div class="flex-shrink-0">
                <div class="w-24 h-24 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                    <x-mary-icon name="o-user" class="w-12 h-12 text-red-600 dark:text-red-400" />
                </div>
            </div>

            <!-- Driver Info -->
            <div class="flex-1">
                <h2 class="text-2xl font-bold mb-2">{{ $driver->full_name }}</h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    @if($driver->team){{ $driver->team->team_name }}@endif
                    @if($driver->nationality) &bull; {{ $driver->nationality }}@endif
                    @if($driver->driver_number) &bull; Driver #{{ $driver->driver_number }}@endif
                </p>

                <!-- Key Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $driver->world_championships ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Championships</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $driver->race_wins ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Wins</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $driver->podiums ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Podiums</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">{{ $driver->pole_positions ?? 0 }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Pole Positions</p>
                    </div>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Driver Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Personal Information -->
        <x-mary-card>
            <h3 class="text-xl font-bold mb-4">Personal Information</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Full Name</p>
                    <p class="font-medium">{{ $driver->full_name }}</p>
                </div>
                @if($driver->date_of_birth)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Date of Birth</p>
                        <p class="font-medium">{{ $driver->date_of_birth->format('F j, Y') }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Age</p>
                        <p class="font-medium">{{ $driver->age }}</p>
                    </div>
                @endif
                @if($driver->nationality)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Nationality</p>
                        <p class="font-medium">{{ $driver->nationality }}</p>
                    </div>
                @endif
                @if($driver->driver_number)
                    <div class="flex justify-between">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Driver Number</p>
                        <p class="font-medium">#{{ $driver->driver_number }}</p>
                    </div>
                @endif
            </div>
        </x-mary-card>

        <!-- Constructor -->
        <x-mary-card>
            <h3 class="text-xl font-bold mb-4">Constructor</h3>
            @if($driver->team)
                <div class="flex items-center space-x-4">
                    <x-constructor-bar :teamName="$driver->team->team_name">
                        <div>
                            <h4 class="font-semibold">{{ $driver->team->team_name }}</h4>
                            @if($driver->driver_number)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Driver #{{ $driver->driver_number }}</p>
                            @endif
                        </div>
                    </x-constructor-bar>
                </div>
                <div class="mt-4">
                    <a href="{{ route('constructor', $driver->team->slug) }}">
                        <x-mary-button variant="outline" size="sm" icon="o-eye">
                            View Constructor
                        </x-mary-button>
                    </a>
                </div>
            @else
                <p class="text-zinc-500 dark:text-zinc-400">No constructor assigned.</p>
            @endif
        </x-mary-card>
    </div>

    <!-- Career Statistics -->
    <x-mary-card>
        <h3 class="text-xl font-bold mb-4">Career Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $driver->world_championships ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Championships</p>
            </div>
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $driver->race_wins ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Wins</p>
            </div>
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $driver->podiums ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Podiums</p>
            </div>
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $driver->pole_positions ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Pole Positions</p>
            </div>
            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $driver->fastest_laps ?? 0 }}</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Fastest Laps</p>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>
