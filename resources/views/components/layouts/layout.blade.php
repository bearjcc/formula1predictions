@props(['title' => null, 'headerSubtitle' => null, 'hideHeader' => false])
@php
  if (! isset($slot)) {
      $slot = '';
  }
  $appearance = session('appearance', config('f1.default_appearance', 'system'));
  $dataTheme = $appearance === 'system' ? null : $appearance;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => $appearance === 'dark']) data-appearance="{{ $appearance }}" @if($dataTheme) data-theme="{{ $dataTheme }}" @endif>
<head>
    @include('partials.head')
</head>
<body class="min-h-screen antialiased bg-white dark:bg-zinc-900 overflow-x-hidden">
    <div class="flex min-h-screen min-w-0">
        <!-- Sidebar - Hidden on mobile, shown on desktop -->
        <div id="sidebar" class="fixed lg:relative inset-y-0 left-0 z-50 w-64 bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <!-- App Logo/Title -->
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 text-zinc-900 dark:text-zinc-100 hover:text-red-600 dark:hover:text-red-400 transition-colors" wire:navigate>
                        <img src="/images/logo.png" alt="F1 Predictor logo" class="h-8 w-auto max-w-full flex-shrink-0" />
                        <h2 class="text-lg font-semibold">F1 Predictor</h2>
                    </a>
                    <!-- Close button for mobile -->
                    <button type="button" id="close-sidebar" class="lg:hidden p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700">
                        <x-mary-icon name="o-x-mark" class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-4 overflow-y-auto">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-2">{{ __('F1 Predictions') }}</h3>
                    <div class="space-y-1">
                        <a href="{{ route('home') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('home') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-home" class="w-4 h-4" />
                            <span>{{ __('Home') }}</span>
                        </a>
                        <a href="{{ route('races', ['year' => config('f1.current_season')]) }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('races') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-calendar" class="w-4 h-4" />
                            <span>{{ __('Races') }}</span>
                        </a>
                        <a href="{{ route('standings', ['year' => config('f1.current_season')]) }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('standings') && !request()->routeIs('standings.predictions') && !request()->routeIs('standings.constructors') && !request()->routeIs('standings.drivers') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-trophy" class="w-4 h-4" />
                            <span>{{ __('Standings') }}</span>
                        </a>
                        <a href="{{ route('predictions.index') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('predictions.*') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-chart-bar" class="w-4 h-4" />
                            <span>{{ __('My Predictions') }}</span>
                        </a>
                        <a href="{{ route('leaderboard.index') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('leaderboard.*') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-trophy" class="w-4 h-4" />
                            <span>{{ __('Leaderboard') }}</span>
                        </a>
                        <a href="{{ route('scoring') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('scoring') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-calculator" class="w-4 h-4" />
                            <span>{{ __('How scoring works') }}</span>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-2">{{ __('Constructors & Drivers') }}</h3>
                    <div class="space-y-1">
                        <a href="{{ route('standings.constructors', ['year' => config('f1.current_season')]) }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('standings.constructors') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-users" class="w-4 h-4" />
                            <span>{{ __('Constructors') }}</span>
                        </a>
                        <a href="{{ route('standings.drivers', ['year' => config('f1.current_season')]) }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('standings.drivers') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-user" class="w-4 h-4" />
                            <span>{{ __('Drivers') }}</span>
                        </a>
                        <a href="{{ route('countries') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('countries') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-map-pin" class="w-4 h-4" />
                            <span>{{ __('Countries') }}</span>
                        </a>
                    </div>
                </div>

                @if(Route::has('components'))
                <div>
                    <h3 class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-2">{{ __('Development') }}</h3>
                    <div class="space-y-1">
                        <a href="{{ route('components') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border-l-2 {{ request()->routeIs('components') ? 'bg-zinc-100 dark:bg-zinc-800 border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 font-medium' : 'border-transparent' }}" wire:navigate>
                            <x-mary-icon name="o-puzzle-piece" class="w-4 h-4" />
                            <span>{{ __('Components') }}</span>
                        </a>
                    </div>
                </div>
                @endif
            </nav>

            <!-- User Menu -->
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                @auth
                    <div class="dropdown dropdown-top w-full">
                        <div tabindex="0" role="button" class="flex items-center justify-between w-full px-3 py-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                            <div class="flex items-center space-x-2">
                                <x-mary-avatar class="w-8 h-8" placeholder="{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}" />
                                <span class="text-sm font-medium truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                            </div>
                            <x-mary-icon name="o-chevron-up" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 dark:bg-zinc-800 rounded-box w-52 mb-2">
                            <li><a href="{{ route('settings.profile') }}" wire:navigate><x-mary-icon name="o-user" class="w-4 h-4" /> {{ __('Profile') }}</a></li>
                            <li><a href="{{ route('settings.appearance') }}" wire:navigate><x-mary-icon name="o-swatch" class="w-4 h-4" /> {{ __('Appearance') }}</a></li>
                            <li><a href="{{ route('feedback') }}" wire:navigate><x-mary-icon name="o-chat-bubble-left-right" class="w-4 h-4" /> {{ __('Feedback') }}</a></li>
                            <li><hr class="my-1 border-zinc-200 dark:border-zinc-700"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="flex items-center space-x-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                        <x-mary-icon name="o-arrow-right-start-on-rectangle" class="w-4 h-4" />
                                        <span>{{ __('Log Out') }}</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <div class="space-y-2">
                        <a href="{{ route('login') }}" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors" wire:navigate>
                            {{ __('Log In') }}
                        </a>
                        <a href="{{ route('register') }}" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-lg transition-colors" wire:navigate>
                            {{ __('Register') }}
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Mobile Overlay -->
        <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 w-full lg:w-auto">
            @if(!$hideHeader)
            <!-- Header -->
            <header class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 min-w-0 flex-1">
                        <button type="button" id="open-sidebar" class="lg:hidden shrink-0 p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700">
                            <x-mary-icon name="o-bars-3" class="w-5 h-5" />
                        </button>
                        <div class="min-w-0">
                            @php
                                $headerTitle = $title ?? $__env->yieldContent('title') ?? config('app.name');
                                $headerSub = $headerSubtitle ?? $__env->yieldContent('headerSubtitle');
                            @endphp
                            <h1 class="text-xl font-semibold truncate text-zinc-900 dark:text-zinc-100">{{ $headerTitle }}</h1>
                            @if($headerSub)
                                <p class="text-sm mt-0.5 truncate text-zinc-600 dark:text-zinc-400">{{ $headerSub }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <livewire:layout.appearance-toggle />
                        @auth
                            <livewire:notifications.notification-dropdown />
                        @endauth

                        <!-- User Menu for Mobile -->
                        <div class="lg:hidden">
                            <div class="dropdown dropdown-bottom dropdown-end">
                                <div tabindex="0" role="button">
                                    @auth
                                        <x-mary-avatar class="w-8 h-8" placeholder="{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}" />
                                    @else
                                        <x-mary-avatar class="w-8 h-8" placeholder="GU" />
                                    @endauth
                                </div>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 dark:bg-zinc-800 rounded-box w-52">
                                    @auth
                                        <li><a href="{{ route('settings.profile') }}" wire:navigate>{{ __('Profile') }}</a></li>
                                        <li><a href="{{ route('feedback') }}" wire:navigate>{{ __('Feedback') }}</a></li>
                                        <li>
                                            <form action="{{ route('logout') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-red-600 w-full text-left">{{ __('Log Out') }}</button>
                                            </form>
                                        </li>
                                    @else
                                        <li><a href="{{ route('login') }}" wire:navigate>{{ __('Log In') }}</a></li>
                                        <li><a href="{{ route('register') }}" wire:navigate>{{ __('Register') }}</a></li>
                                    @endauth
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            @else
            <!-- Mobile menu button when header is hidden (e.g. homepage) -->
            <div class="lg:hidden fixed top-4 left-4 z-30">
                <button type="button" id="open-sidebar" class="p-2 rounded-lg bg-white/90 dark:bg-zinc-800/90 shadow hover:bg-zinc-100 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-600" aria-label="{{ __('Open menu') }}">
                    <x-mary-icon name="o-bars-3" class="w-5 h-5 text-zinc-700 dark:text-zinc-300" />
                </button>
            </div>
            @endif

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6 overflow-auto min-w-0">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
    </div>

    <script data-navigate-once>
        // Set user ID for notifications
        @auth
            window.userId = {{ Auth::id() }};
        @endauth

        // Mobile sidebar functionality
        document.addEventListener('livewire:navigated', function() {
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobile-overlay');
            const openSidebarBtn = document.getElementById('open-sidebar');
            const closeSidebarBtn = document.getElementById('close-sidebar');

            function openSidebar() {
                if (sidebar) sidebar.classList.remove('-translate-x-full');
                if (mobileOverlay) mobileOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.add('-translate-x-full');
                if (mobileOverlay) mobileOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }

            if (openSidebarBtn) openSidebarBtn.addEventListener('click', openSidebar);
            if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeSidebar);
            if (mobileOverlay) mobileOverlay.addEventListener('click', closeSidebar);

            // Close sidebar when clicking on navigation links on mobile
            if (sidebar) {
                const navLinks = sidebar.querySelectorAll('a[wire\\:navigate]');
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 1024) { // lg breakpoint
                            closeSidebar();
                        }
                    });
                });
            }

            // Handle window resize
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    closeSidebar();
                }
            });
        });
    </script>

    @livewireScripts
</body>
</html>