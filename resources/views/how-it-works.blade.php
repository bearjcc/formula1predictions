<x-layouts.layout title="How F1 Predictions Works" headerSubtitle="See how to play, get scored, and track your rank.">
    <div class="max-w-4xl mx-auto space-y-10">
        <section class="pt-4">
            <h2 class="text-heading-1 mb-4">The basic loop</h2>
            <p class="text-lg text-zinc-700 dark:text-zinc-300 mb-4">
                F1 Predictions is a simple loop: make picks, let races happen, get scored, and see how you rank against everyone else.
            </p>
            <ol class="space-y-3 text-zinc-800 dark:text-zinc-200 list-decimal list-inside">
                <li><strong>Create an account</strong> so we can track your predictions and scores.</li>
                <li><strong>Make predictions</strong> for upcoming races or the season.</li>
                <li><strong>Wait for results</strong> &mdash; the site scores your predictions automatically.</li>
                <li><strong>Check standings and leaderboards</strong> to see how you&rsquo;re doing over time.</li>
            </ol>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-mary-card class="p-6">
                <h3 class="text-heading-3 mb-2">Step 1: Create your account</h3>
                <p class="text-auto-muted mb-4">
                    You&rsquo;ll need an account so we can attach predictions, scores, and leaderboard positions to you.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-primary" wire:navigate>Register</a>
                    <a href="{{ route('login') }}" class="btn btn-outline" wire:navigate>Log in</a>
                </div>
            </x-mary-card>

            <x-mary-card class="p-6">
                <h3 class="text-heading-3 mb-2">Step 2: Make predictions</h3>
                <p class="text-auto-muted mb-4">
                    Pick finishing order, fastest lap, and season outcomes. You can start with the next race or make a preseason prediction.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ auth()->check() ? route('predict.create') : route('login') }}" class="btn btn-primary" wire:navigate>
                        {{ auth()->check() ? 'Make a prediction' : 'Log in to predict' }}
                    </a>
                    <a href="{{ route('races', ['year' => config('f1.current_season')]) }}" class="btn btn-outline" wire:navigate>
                        View race schedule
                    </a>
                </div>
            </x-mary-card>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-mary-card class="p-6">
                <h3 class="text-heading-3 mb-2">Step 3: See how scoring works</h3>
                <p class="text-auto-muted mb-4">
                    Points depend on how close your picks are to the real results, with bonuses for things like fastest lap and perfect predictions.
                </p>
                <a href="{{ route('scoring') }}" class="btn btn-outline" wire:navigate>
                    View scoring rules
                </a>
            </x-mary-card>

            <x-mary-card class="p-6">
                <h3 class="text-heading-3 mb-2">Step 4: Track your rank</h3>
                <p class="text-auto-muted mb-4">
                    Use standings pages and the leaderboard to see where you sit in the championship and how your scores evolve across races.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('standings.predictions', ['year' => config('f1.current_season')]) }}" class="btn btn-outline" wire:navigate>
                        Prediction standings
                    </a>
                    <a href="{{ route('leaderboard.index') }}" class="btn btn-outline" wire:navigate>
                        Global leaderboard
                    </a>
                </div>
            </x-mary-card>
        </section>

        @auth
            <section>
                <x-mary-card class="p-6">
                    <h3 class="text-heading-3 mb-2">Your hub</h3>
                    <p class="text-auto-muted mb-4">
                        Once you understand the basics, you&rsquo;ll probably live in your dashboard and predictions list.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary" wire:navigate>Go to dashboard</a>
                        <a href="{{ route('predictions.index') }}" class="btn btn-outline" wire:navigate>View your predictions</a>
                    </div>
                </x-mary-card>
            </section>
        @endauth
    </div>
</x-layouts.layout>

