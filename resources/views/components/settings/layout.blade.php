<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <nav class="space-y-1">
            <a href="{{ route('settings.profile') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('settings.profile') ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}" wire:navigate>{{ __('Profile') }}</a>
            <a href="{{ route('settings.password') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('settings.password') ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}" wire:navigate>{{ __('Password') }}</a>
            <a href="{{ route('settings.appearance') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('settings.appearance') ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}" wire:navigate>{{ __('Appearance') }}</a>
        </nav>
    </div>

    <hr class="md:hidden my-4 border-zinc-200 dark:border-zinc-700" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <h2 class="text-2xl font-bold">{{ $heading ?? '' }}</h2>
        <p class="text-zinc-600 dark:text-zinc-400 mt-1">{{ $subheading ?? '' }}</p>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
