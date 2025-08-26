@props(['title' => 'F1 Predictor'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-900">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 flex flex-col">
            <!-- App Logo/Title -->
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('home') }}" class="flex items-center space-x-2" wire:navigate>
                    <h2 class="text-lg font-semibold">🏎️ F1 Predictor</h2>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-2">{{ __('F1 Predictions') }}</h3>
                    <div class="space-y-1">
                        <a href="{{ route('home') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('home') ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}" wire:navigate>
                            <x-mary-icon name="o-home" class="w-4 h-4" />
                            <span>{{ __('Home') }}</span>
                        </a>
                        <a href="{{ route('races', ['year' => '2025']) }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            <x-mary-icon name="o-calendar" class="w-4 h-4" />
                            <span>{{ __('Races') }}</span>
                        </a>
                        <a href="{{ route('standings', ['year' => '2025']) }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            <x-mary-icon name="o-trophy" class="w-4 h-4" />
                            <span>{{ __('Standings') }}</span>
                        </a>
                        <a href="{{ route('standings.predictions', ['year' => '2025']) }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            <x-mary-icon name="o-chart-bar" class="w-4 h-4" />
                            <span>{{ __('Predictions') }}</span>
                        </a>
                        <a href="{{ route('leaderboard.index') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('leaderboard.*') ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}" wire:navigate>
                            <x-mary-icon name="o-trophy" class="w-4 h-4" />
                            <span>{{ __('Leaderboard') }}</span>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-2">{{ __('Teams & Drivers') }}</h3>
                    <div class="space-y-1">
                        <a href="{{ route('standings.teams', ['year' => '2025']) }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            <x-mary-icon name="o-users" class="w-4 h-4" />
                            <span>{{ __('Teams') }}</span>
                        </a>
                        <a href="{{ route('standings.drivers', ['year' => '2025']) }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            <x-mary-icon name="o-user" class="w-4 h-4" />
                            <span>{{ __('Drivers') }}</span>
                        </a>
                        <a href="{{ route('countries') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            <x-mary-icon name="o-map-pin" class="w-4 h-4" />
                            <span>{{ __('Countries') }}</span>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-2">{{ __('Development') }}</h3>
                    <div class="space-y-1">
                        <a href="{{ route('components') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('components') ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}" wire:navigate>
                            <x-mary-icon name="o-puzzle-piece" class="w-4 h-4" />
                            <span>{{ __('Components') }}</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- User Menu -->
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                <div class="dropdown dropdown-top">
                    <div tabindex="0" role="button" class="flex items-center space-x-2 cursor-pointer">
                        <x-mary-avatar class="w-8 h-8" placeholder="GU" />
                        <span class="text-sm">Guest User</span>
                        <x-mary-icon name="o-chevron-down" class="w-4 h-4" />
                    </div>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        <li><a href="{{ route('login') }}" wire:navigate>{{ __('Log In') }}</a></li>
                        <li><a href="{{ route('register') }}" wire:navigate>{{ __('Register') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button class="lg:hidden p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700">
                            <x-mary-icon name="o-bars-3" class="w-5 h-5" />
                        </button>
                        <h1 class="text-xl font-semibold">{{ $title }}</h1>
                    </div>

                    <!-- User Menu for Mobile -->
                    <div class="lg:hidden">
                        <div class="dropdown dropdown-bottom dropdown-end">
                            <div tabindex="0" role="button">
                                <x-mary-avatar class="w-8 h-8" placeholder="GU" />
                            </div>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                <li><a href="{{ route('login') }}" wire:navigate>{{ __('Log In') }}</a></li>
                                <li><a href="{{ route('register') }}" wire:navigate>{{ __('Register') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 overflow-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>