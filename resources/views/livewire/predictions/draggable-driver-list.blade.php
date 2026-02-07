<div class="w-full">
    @if(!empty($drivers))
    <div 
        x-data="{
            drivers: @js($drivers),
            driverOrder: @js($driverOrder),
            fastestLap: @js($fastestLapDriverId),
            draggedIndex: null,
            draggedOverIndex: null,
            
            dragStart(index) {
                this.draggedIndex = index;
            },
            
            dragOver(e, index) {
                e.preventDefault();
                this.draggedOverIndex = index;
            },
            
            dragEnd() {
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
        class="space-y-4"
    >
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-zinc-900 dark:text-white">{{ $raceName }} Predicted Order</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Drag to reorder. Points awarded based on proximity to actual finish.</p>
                </div>
                <div class="hidden sm:block">
                    <x-mary-badge value="Pro Tip" class="bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400" />
                </div>
            </div>
            
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                <template x-for="(driverId, index) in driverOrder" :key="driverId">
                    <div 
                        x-show="getDriverById(driverId)"
                        :class="{
                            'bg-blue-50/50 dark:bg-blue-900/10': draggedOverIndex === index,
                            'opacity-40': draggedIndex === index
                        }"
                        class="group p-3 sm:p-4 cursor-move hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-all duration-150 relative select-none"
                        draggable="true"
                        @dragstart="dragStart(index)"
                        @dragover="dragOver($event, index)"
                        @dragleave="draggedOverIndex = null"
                        @drop="drop($event, index)"
                        @dragend="dragEnd()"
                    >
                        <div class="flex items-center space-x-3 sm:space-x-5">
                            <!-- Position Number -->
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center font-bold"
                                 :class="index === 0 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400' : 
                                         (index === 1 ? 'bg-slate-200 text-slate-700 dark:bg-slate-700/60 dark:text-slate-300' : 
                                         (index === 2 ? 'bg-amber-100 text-amber-700 dark:bg-amber-800/20 dark:text-amber-500' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500'))">
                                <span x-text="index + 1"></span>
                            </div>
                            
                            <!-- Driver Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 truncate" x-text="getDriverById(driverId)?.name + ' ' + getDriverById(driverId)?.surname"></span>
                                    <span class="text-[10px] uppercase font-semibold text-zinc-400 tracking-wider" x-text="getDriverById(driverId)?.nationality?.substring(0, 3)"></span>
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium" x-text="getDriverById(driverId)?.team?.team_name || 'Individual Entry'"></div>
                            </div>
                            
                            <!-- Fastest Lap Button -->
                            <button
                                type="button"
                                @click="setFastestLap(driverId)"
                                :class="fastestLap === driverId ? 'bg-red-600 text-white shadow-lg shadow-red-600/20' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-600 hover:bg-zinc-200 dark:hover:bg-zinc-700'"
                                class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center transition-all duration-200"
                                :title="fastestLap === driverId ? 'Predicted Fastest Lap' : 'Set as Fastest Lap'"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </button>

                            <!-- Drag Handle (Mobile only visibility, always functional) -->
                            <div class="flex-shrink-0 text-zinc-300 dark:text-zinc-600 group-hover:text-zinc-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Desktop Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <p class="text-xs uppercase font-bold text-zinc-400 tracking-widest mb-1">Fastest Lap</p>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-red-600 animate-pulse" x-show="fastestLap"></div>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100" x-text="fastestLap ? (getDriverById(fastestLap)?.name + ' ' + getDriverById(fastestLap)?.surname) : 'No driver selected'"></span>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 flex flex-col justify-center">
                <p class="text-xs uppercase font-bold text-zinc-400 tracking-widest mb-1">Top Prediction</p>
                <span class="font-bold text-zinc-900 dark:text-zinc-100" x-text="driverOrder.length > 0 ? getDriverById(driverOrder[0])?.surname : '-'"></span>
            </div>
            
            <div class="hidden lg:block bg-zinc-900 p-4 rounded-lg border border-zinc-800 overflow-hidden relative group">
                <div class="relative z-10">
                    <p class="text-xs uppercase font-bold text-zinc-500 tracking-widest mb-1">Status</p>
                    <p class="text-sm font-bold text-white">Live Sync Active</p>
                </div>
                <div class="absolute top-1/2 right-4 -translate-y-1/2 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-900 rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
            <x-mary-icon name="o-user-group" class="w-12 h-12 text-zinc-300 mx-auto mb-3" />
            <p class="text-zinc-500">No active drivers found for this prediction.</p>
        </div>
    @endif
</div>
