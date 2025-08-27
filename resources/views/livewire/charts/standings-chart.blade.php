<div class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $chartType === 'driver' ? 'Driver' : 'Team' }} Standings Progression
            </h3>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $season }} Season
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex items-center space-x-2">
                <label for="chart-type" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Type:
                </label>
                <select 
                    id="chart-type"
                    wire:model.live="chartType"
                    class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-md px-3 py-1 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="driver">Drivers</option>
                    <option value="team">Teams</option>
                </select>
            </div>
            
            <div class="flex items-center space-x-2">
                <label for="season" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Season:
                </label>
                <select 
                    id="season"
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
                <p class="text-sm">No {{ $chartType }} standings data found for {{ $season }} season.</p>
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
                const labels = chartData.map(item => item.race || item.date);
                const datasets = [];
                
                // Get all unique drivers/teams
                const entities = new Set();
                chartData.forEach(item => {
                    Object.keys(item).forEach(key => {
                        if (key !== 'race' && key !== 'round' && key !== 'date') {
                            entities.add(key);
                        }
                    });
                });
                
                // Create datasets for each entity
                const colors = [
                    '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
                    '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1',
                    '#14B8A6', '#F43F5E', '#A855F7', '#0EA5E9', '#22C55E'
                ];
                
                let colorIndex = 0;
                entities.forEach(entity => {
                    const data = chartData.map(item => item[entity] || null);
                    datasets.push({
                        label: entity,
                        data: data,
                        borderColor: colors[colorIndex % colors.length],
                        backgroundColor: colors[colorIndex % colors.length] + '20',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    });
                    colorIndex++;
                });
                
                chart = new Chart(ctx, {
                    type: 'line',
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
                                reverse: true,
                                title: {
                                    display: true,
                                    text: 'Position'
                                },
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Race'
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
                                        return context.dataset.label + ': Position ' + context.parsed.y;
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
