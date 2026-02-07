@props([])
<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prediction Summary</h5>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $summary ?? '' }}
            </div>
        </div>
        <div>
            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Top 3 Prediction</h5>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $top3 ?? '' }}
            </div>
        </div>
    </div>
</div>
