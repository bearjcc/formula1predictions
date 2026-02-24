@props([])
<div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h5 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Prediction Summary</h5>
            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $summary ?? '' }}
            </div>
        </div>
        <div>
            <h5 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Top 3 Prediction</h5>
            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $top3 ?? '' }}
            </div>
        </div>
    </div>
</div>
