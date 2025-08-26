@props(['title' => 'F1 Predictor'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
    <!-- Main Layout with Sidebar -->
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900" x-data="{ open: false }" x-show="open" x-cloak>
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <!-- App Logo/Title -->
        <a href="{{ route('home') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <flux:heading size="lg">🏎️ F1 Predictor</flux:heading>
        </a>

        <!-- Main Navigation -->
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('F1 Predictions')" class="grid">
                <flux:navlist.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
                    {{ __('Home') }}
                </flux:navlist.item>
                <flux:navlist.item icon="calendar" :href="route('races', ['year' => '2024'])" wire:navigate>
                    {{ __('Races') }}
                </flux:navlist.item>
                <flux:navlist.item icon="trophy" :href="route('standings', ['year' => '2024'])" wire:navigate>
                    {{ __('Standings') }}
                </flux:navlist.item>
                <flux:navlist.item icon="chart-bar" :href="route('standings.predictions', ['year' => '2024'])" wire:navigate>
                    {{ __('Predictions') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Teams & Drivers')" class="grid">
                <flux:navlist.item icon="users" :href="route('standings.teams', ['year' => '2024'])" wire:navigate>
                    {{ __('Teams') }}
                </flux:navlist.item>
                <flux:navlist.item icon="user" :href="route('standings.drivers', ['year' => '2024'])" wire:navigate>
                    {{ __('Drivers') }}
                </flux:navlist.item>
                <flux:navlist.item icon="map-pin" :href="route('countries')" wire:navigate>
                    {{ __('Countries') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Development')" class="grid">
                <flux:navlist.item icon="puzzle-piece" :href="route('components')" :current="request()->routeIs('components')" wire:navigate>
                    {{ __('Components') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <!-- User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile
                name="Guest User"
                initials="GU"
                icon:trailing="chevrons-up-down"
            />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    GU
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">Guest User</span>
                                <span class="truncate text-xs">guest@example.com</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('login')" icon="arrow-right-start-on-rectangle" wire:navigate>
                        {{ __('Log In') }}
                    </flux:menu.item>
                    <flux:menu.item :href="route('register')" icon="user-plus" wire:navigate>
                        {{ __('Register') }}
                    </flux:menu.item>
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile Header -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" @click="open = !open" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile
                initials="GU"
                icon-trailing="chevron-down"
            />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    GU
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">Guest User</span>
                                <span class="truncate text-xs">guest@example.com</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('login')" icon="arrow-right-start-on-rectangle" wire:navigate>
                        {{ __('Log In') }}
                    </flux:menu.item>
                    <flux:menu.item :href="route('register')" icon="user-plus" wire:navigate>
                        {{ __('Register') }}
                    </flux:menu.item>
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <!-- Desktop Header with Navigation -->
    <flux:header class="hidden lg:block border-b border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center justify-between w-full px-6">
            <!-- Left side - Menu toggle and logo -->
            <div class="flex items-center space-x-4">
                <flux:button 
                    variant="ghost" 
                    size="sm" 
                    icon-leading="bars-3" 
                    @click="open = !open"
                    class="lg:hidden"
                >
                    Menu
                </flux:button>
                
                <a href="{{ route('home') }}" class="flex items-center space-x-2" wire:navigate>
                    <flux:heading size="lg">🏎️ F1 Predictor</flux:heading>
                </a>
            </div>

            <!-- Center - Navigation Links -->
            <nav class="hidden lg:flex items-center space-x-8">
                <a href="{{ route('races', ['year' => '2024']) }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" wire:navigate>
                    Races
                </a>
                <a href="{{ route('standings', ['year' => '2024']) }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" wire:navigate>
                    Standings
                </a>
                <a href="{{ route('standings.predictions', ['year' => '2024']) }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" wire:navigate>
                    Predictions
                </a>
                <a href="{{ route('standings.teams', ['year' => '2024']) }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" wire:navigate>
                    Teams
                </a>
                <a href="{{ route('standings.drivers', ['year' => '2024']) }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" wire:navigate>
                    Drivers
                </a>
                <a href="{{ route('countries') }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" wire:navigate>
                    Countries
                </a>
            </nav>

            <!-- Right side - User menu -->
            <flux:dropdown position="bottom" align="end">
                <flux:profile
                    name="Guest User"
                    initials="GU"
                    icon-trailing="chevron-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        GU
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">Guest User</span>
                                    <span class="truncate text-xs">guest@example.com</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('login')" icon="arrow-right-start-on-rectangle" wire:navigate>
                            {{ __('Log In') }}
                        </flux:menu.item>
                        <flux:menu.item :href="route('register')" icon="user-plus" wire:navigate>
                            {{ __('Register') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>
                </flux:menu>
            </flux:dropdown>
        </div>
    </flux:header>

    <!-- Main Content -->
    <flux:main>
        <div class="p-6">
            {{ $slot }}
        </div>
    </flux:main>

    @fluxScripts
</body>
</html>