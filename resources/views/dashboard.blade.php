{{-- TODO: Create user dashboard with personalized F1 content --}}
{{-- TODO: Add user's prediction statistics and performance --}}
{{-- TODO: Show upcoming races and user's predictions --}}
{{-- TODO: Display recent race results and user's accuracy --}}
{{-- TODO: Add leaderboard showing user's position --}}
{{-- TODO: Include F1 news and updates feed --}}
{{-- TODO: Add quick access to user's favorite drivers/teams --}}
{{-- TODO: Show user's prediction history and trends --}}
{{-- TODO: Add notification center for race reminders --}}

<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- TODO: Replace placeholder cards with actual dashboard widgets --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                {{-- TODO: Add user's prediction accuracy widget --}}
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                {{-- TODO: Add upcoming race countdown widget --}}
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                {{-- TODO: Add user's leaderboard position widget --}}
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            {{-- TODO: Add main dashboard content area (recent predictions, news, etc.) --}}
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
