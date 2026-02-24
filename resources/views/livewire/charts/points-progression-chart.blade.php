<div>
    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Points Progression</h3>
    <div class="mb-4">
        <label for="chart-type-{{ $chartId }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            Chart Type:
        </label>
        <select
            id="chart-type-{{ $chartId }}"
            wire:model.live="chartType"
            class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
        >
            <option value="driver">Driver Points</option>
            <option value="team">Constructor Points</option>
        </select>
    </div>

    <div class="h-80">
        <canvas id="{{ $chartId }}"></canvas>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('{{ $chartId }}');
            if (ctx && ctx.chart) {
                ctx.chart.destroy();
            }

            if (ctx) {
                ctx.chart = new Chart(ctx, {
                    type: 'line',
                    data: @json($chartData),
                    options: @json($chartConfig['options'])
                });
            }
        });
    </script>
</div>