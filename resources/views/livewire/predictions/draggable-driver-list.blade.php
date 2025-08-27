<div class="w-full">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ $raceName }} - Driver Predictions
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Drag drivers to reorder your predictions. Click on a driver to set fastest lap.
        </p>
    </div>

    <div 
        x-data="{
            drivers: @js($drivers),
            driverOrder: @js($driverOrder),
            fastestLap: @js($fastestLapDriverId),
            draggedIndex: null,
            draggedOverIndex: null,
            
            dragStart(index) {
                this.draggedIndex = index;
                this.$el.classList.add('opacity-50');
            },
            
            dragOver(e, index) {
                e.preventDefault();
                this.draggedOverIndex = index;
            },
            
            dragEnd() {
                this.$el.classList.remove('opacity-50');
                this.draggedIndex = null;
                this.draggedOverIndex = null;
            },
            
            drop(e, dropIndex) {
                e.preventDefault();
                if (this.draggedIndex !== null && this.draggedIndex !== dropIndex) {
                    const newOrder = [...this.driverOrder];
                    const [draggedItem] = newOrder.splice(this.draggedIndex, 1);
                    newOrder.splice(dropIndex, 0, draggedItem);
                    this.driverOrder = newOrder;
                    
                    // Update Livewire component
                    $wire.updateDriverOrder(newOrder);
                }
                this.draggedIndex = null;
                this.draggedOverIndex = null;
            },
            
            setFastestLap(driverId) {
                this.fastestLap = this.fastestLap === driverId ? null : driverId;
                $wire.setFastestLap(this.fastestLap);
            },
            
            getDriverById(id) {
                return this.drivers.find(driver => driver.id === id);
            }
        }"
        class="space-y-2"
    >
        <!-- Driver List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h4 class="font-medium text-gray-900 dark:text-white">Driver Order (Drag to Reorder)</h4>
            </div>
            
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="(driverId, index) in driverOrder" :key="driverId">
                    <div 
                        x-show="getDriverById(driverId)"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        :class="{
                            'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700': draggedOverIndex === index,
                            'bg-gray-50 dark:bg-gray-700': draggedOverIndex === index
                        }"
                        class="p-4 cursor-move hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
                        draggable="true"
                        @dragstart="dragStart(index)"
                        @dragover="dragOver($event, index)"
                        @dragleave="draggedOverIndex = null"
                        @drop="drop($event, index)"
                        @dragend="dragEnd()"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 sm:space-x-4">
                                <!-- Position Number -->
                                <div class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="index + 1"></span>
                                </div>
                                
                                <!-- Driver Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="getDriverById(driverId)?.name + ' ' + getDriverById(driverId)?.surname"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="getDriverById(driverId)?.nationality"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="getDriverById(driverId)?.team?.team_name || 'Unknown Team'"></div>
                                </div>
                            </div>
                            
                            <!-- Fastest Lap Button -->
                            <button
                                @click="setFastestLap(driverId)"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': fastestLap === driverId,
                                    'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500': fastestLap !== driverId
                                }"
                                class="px-3 py-1 text-xs font-medium rounded-full transition-colors duration-150"
                            >
                                <span x-show="fastestLap === driverId">Fastest Lap ✓</span>
                                <span x-show="fastestLap !== driverId">Set FL</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prediction Summary</h5>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p>Total Drivers: <span class="font-medium" x-text="driverOrder.length"></span></p>
                        <p>Fastest Lap: <span class="font-medium" x-text="fastestLap ? (getDriverById(fastestLap)?.name + ' ' + getDriverById(fastestLap)?.surname) : 'Not set'"></span></p>
                    </div>
                </div>
                
                <div>
                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Top 3 Prediction</h5>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <template x-for="(driverId, index) in driverOrder.slice(0, 3)" :key="driverId">
                            <p><span class="font-medium" x-text="index + 1"></span>. <span x-text="getDriverById(driverId)?.name + ' ' + getDriverById(driverId)?.surname"></span></p>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
            <div class="flex items-start space-x-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-medium">How to use:</p>
                    <ul class="mt-1 space-y-1">
                        <li>• Drag drivers up or down to reorder your predictions</li>
                        <li>• Click "Set FL" to mark a driver for fastest lap</li>
                        <li>• Your prediction will be automatically saved</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

