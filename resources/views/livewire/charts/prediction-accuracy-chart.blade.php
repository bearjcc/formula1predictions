<div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                Prediction Accuracy Analytics
            </h3>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $season }} Season
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex items-center space-x-2">
                <label for="accuracy-chart-type" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    View:
                </label>
                <select 
                    id="accuracy-chart-type"
                    wire:model.live="chartType"
                    class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-md px-3 py-1 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="user-trends">My Trends</option>
                    <option value="user-comparison">User Comparison</option>
                    <option value="race-accuracy">Race Accuracy</option>
                    <option value="predictor-luck-variance">Luck &amp; Variance</option>
                </select>
            </div>
            
            <div class="flex items-center space-x-2">
                <label for="accuracy-season" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Season:
                </label>
                <select 
                    id="accuracy-season"
                    wire:model.live="season"
                    class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-md px-3 py-1 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                </select>
            </div>
        </div>
    </div>

    <div class="relative" style="height: 300px; sm:height: 400px;">
        <canvas id="{{ $chartId }}" wire:ignore></canvas>
    </div>

    @if (empty($chartData))
        <div class="flex items-center justify-center h-64 text-zinc-500 dark:text-zinc-400">
            <div class="text-center">
                <x-mary-icon name="o-chart-bar" class="w-12 h-12 mx-auto mb-4 opacity-50" />
                <p class="text-lg font-medium">No data available</p>
                <p class="text-sm">
                    @if ($chartType === 'user-trends')
                        No prediction accuracy data found for your account in {{ $season }} season.
                    @elseif ($chartType === 'user-comparison')
                        No user comparison data found for {{ $season }} season.
                    @elseif ($chartType === 'predictor-luck-variance')
                        No luck &amp; variance data found for {{ $season }} season.
                    @else
                        No race accuracy data found for {{ $season }} season.
                    @endif
                </p>
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
                
                let labels, datasets, chartType, yAxisLabel, isPercentScale = true;
                
                // Prepare data based on chart type
                switch ('{{ $chartType }}') {
                    case 'user-trends':
                        labels = chartData.map(item => item.date);
                        datasets = [{
                            label: 'Accuracy (%)',
                            data: chartData.map(item => item.accuracy),
                            borderColor: '#3B82F6',
                            backgroundColor: '#3B82F620',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.1,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }];
                        chartType = 'line';
                        yAxisLabel = 'Accuracy (%)';
                        break;
                        
                    case 'user-comparison':
                        labels = chartData.map(item => item.user);
                        datasets = [{
                            label: 'Average Accuracy (%)',
                            data: chartData.map(item => item.avg_accuracy),
                            backgroundColor: [
                                '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
                                '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1'
                            ],
                            borderWidth: 1,
                            borderColor: '#ffffff',
                        }];
                        chartType = 'bar';
                        yAxisLabel = 'Accuracy (%)';
                        break;
                        
                    case 'race-accuracy':
                        labels = chartData.map(item => item.race);
                        datasets = [{
                            label: 'Average Accuracy (%)',
                            data: chartData.map(item => item.avg_accuracy),
                            backgroundColor: '#10B981',
                            borderColor: '#10B981',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.1,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }];
                        chartType = 'line';
                        yAxisLabel = 'Accuracy (%)';
                        break;
                    
                    case 'predictor-luck-variance':
                        labels = chartData.map(item => item.user);
                        datasets = [
                            {
                                label: 'Total Score',
                                data: chartData.map(item => item.total_score),
                                backgroundColor: '#3B82F6',
                                borderWidth: 1,
                                borderColor: '#ffffff',
                            },
                            {
                                label: 'Luck Index',
                                data: chartData.map(item => item.luck_index),
                                backgroundColor: '#F59E0B',
                                borderWidth: 1,
                                borderColor: '#ffffff',
                            }
                        ];
                        chartType = 'bar';
                        yAxisLabel = 'Points';
                        isPercentScale = false;
                        break;
                        
                    default:
                        return;
                }
                
                chart = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: labels,
                        datasets: datasets
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
                                ...(isPercentScale ? { max: 100 } : {}),
                                title: {
                                    display: true,
                                    text: yAxisLabel
                                },
                                ticks: isPercentScale ? {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                } : {}
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: chartType === 'line' ? 'Date' : 'Users'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        const suffix = isPercentScale ? '%' : '';
                                        return context.dataset.label + ': ' + context.parsed.y + suffix;
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
