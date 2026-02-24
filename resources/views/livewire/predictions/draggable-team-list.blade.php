<div class="w-full">
    <div 
        x-data="{
            teams: @js($teams),
            teamOrder: @js($teamOrder),
            constructorColors: @js(config('constructor_colors')),
            draggedIndex: null,
            draggedOverIndex: null,

            getConstructorColor(teamName) {
                if (!teamName || !this.constructorColors) return null;
                const k = Object.keys(this.constructorColors).find(key => key.trim().toLowerCase() === String(teamName).trim().toLowerCase());
                return k ? this.constructorColors[k] : null;
            },
            
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
                    const newOrder = [...this.teamOrder];
                    const [draggedItem] = newOrder.splice(this.draggedIndex, 1);
                    newOrder.splice(dropIndex, 0, draggedItem);
                    this.teamOrder = newOrder;
                    
                    // Update Livewire component
                    $wire.updateTeamOrder(newOrder);
                }
                this.draggedIndex = null;
                this.draggedOverIndex = null;
            },
            
            getTeamById(id) {
                return this.teams.find(team => team.id === id);
            }
        }"
        class="space-y-2"
    >
        <!-- Team List -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <h4 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</h4>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Constructor order (drag to reorder). Drag constructors to reorder your predictions.</p>
            </div>
            
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                <template x-for="(teamId, index) in teamOrder" :key="teamId">
                    <div 
                        x-show="getTeamById(teamId)"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        :class="{
                            'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700': draggedOverIndex === index
                        }"
                        class="p-4 cursor-move hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-150"
                        draggable="true"
                        @dragstart="dragStart(index)"
                        @dragover="dragOver($event, index)"
                        @dragleave="draggedOverIndex = null"
                        @drop="drop($event, index)"
                        @dragend="dragEnd()"
                    >
                            <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 min-w-0">
                                <!-- Position Number -->
                                <div class="flex-shrink-0 w-8 h-8 bg-zinc-100 dark:bg-zinc-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300" x-text="index + 1"></span>
                                </div>
                                <!-- Constructor color bar -->
                                <span x-show="getConstructorColor(getTeamById(teamId)?.team_name)" class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[1.25rem]" :style="getConstructorColor(getTeamById(teamId)?.team_name) ? 'background-color: ' + getConstructorColor(getTeamById(teamId)?.team_name) : ''" aria-hidden="true"></span>
                                <!-- Team Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100" x-text="getTeamById(teamId)?.display_name || getTeamById(teamId)?.team_name"></span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="getTeamById(teamId)?.driver_surnames || getTeamById(teamId)?.nationality"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
