<div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                Driver Consistency Analysis
            </h3>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $season }} Season - Based on position standard deviation
            </p>
        </div>
        
        <div class="flex items-center space-x-2">
            <label for="consistency-season" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                Season:
            </label>
            <select 
                id="consistency-season"
                wire:model.live="season"
                class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-md px-3 py-1 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-red-600 dark:focus:ring-red-500 focus:border-red-600 dark:focus:border-red-500"
            >
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
            </select>
        </div>
    </div>

    <div class="relative" style="height: 400px;">
        <canvas id="{{ $chartId }}" wire:ignore></canvas>
    </div>

    @if (empty($chartData))
        <div class="flex items-center justify-center h-64 text-zinc-500 dark:text-zinc-400">
            <div class="text-center">
                <x-mary-icon name="o-chart-bar" class="w-12 h-12 mx-auto mb-4 opacity-50" />
                <p class="text-lg font-medium">No data available</p>
                <p class="text-sm">No driver consistency data found for {{ $season }} season.</p>
            </div>
        </div>
    @else
        <!-- Additional Stats -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            @php
                $topConsistent = $chartData[0] ?? null;
                $avgConsistency = array_sum(array_column($chartData, 'consistency_score')) / count($chartData);
                $mostRaces = max(array_column($chartData, 'races'));
            @endphp
            
            @if($topConsistent)
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Most Consistent</h4>
                    <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ $topConsistent['driver'] }}</p>
                    <p class="text-sm text-blue-700 dark:text-blue-300">{{ $topConsistent['consistency_score'] }}% consistency</p>
                </div>
            @endif
            
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <h4 class="text-sm font-medium text-green-800 dark:text-green-200">Average Consistency</h4>
                <p class="text-lg font-bold text-green-900 dark:text-green-100">{{ round($avgConsistency, 1) }}%</p>
                <p class="text-sm text-green-700 dark:text-green-300">Across all drivers</p>
            </div>
            
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                <h4 class="text-sm font-medium text-purple-800 dark:text-purple-200">Most Races</h4>
                <p class="text-lg font-bold text-purple-900 dark:text-purple-100">{{ $mostRaces }}</p>
                <p class="text-sm text-purple-700 dark:text-purple-300">Races completed</p>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', function () {
            let chart = null;
            
            function initChart() {
                const ctx = document.getElementById('{{ $chartId }}');
                if (!ctx) return;
                
                // Destroy existing chart if it exists
                if (chart) {
                    chart.destroy();
                }
                
                const chartData = @json($chartData);
                
                if (chartData.length === 0) return;
                
                // Prepare data for Chart.js
                const labels = chartData.map(item => item.driver);
                const consistencyScores = chartData.map(item => item.consistency_score);
                const avgPositions = chartData.map(item => item.avg_position);
                
                // Create gradient for bars
                const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0.4)');
                
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Consistency Score (%)',
                            data: consistencyScores,
                            backgroundColor: gradient,
                            borderColor: '#3B82F6',
                            borderWidth: 1,
                            borderRadius: 4,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Consistency Score (%)'
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.1)',
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Drivers'
                                },
                                grid: {
                                    display: false,
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: 'rgba(255, 255, 255, 0.1)',
                                borderWidth: 1,
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        const dataIndex = context.dataIndex;
                                        const data = chartData[dataIndex];
                                        return [
                                            'Consistency: ' + context.parsed.y + '%',
                                            'Avg Position: ' + data.avg_position,
                                            'Std Deviation: ' + data.std_deviation,
                                            'Races: ' + data.races
                                        ];
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Initialize chart when component loads
            initChart();
            
            // Re-initialize chart when data updates
            Livewire.on('chart-data-updated', function () {
                setTimeout(initChart, 100);
            });
        });
    </script>
</div>
