<div class="w-full">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
            {{ $title }}
        </h3>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
            Drag constructors to reorder your predictions.
        </p>
    </div>

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
                <h4 class="font-medium text-zinc-900 dark:text-zinc-100">Constructor order (drag to reorder)</h4>
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

        <!-- Summary -->
        <x-prediction-summary>
            <x-slot:summary>
                <p>Total constructors: <span class="font-medium" x-text="teamOrder.length"></span></p>
            </x-slot:summary>
            <x-slot:top3>
                <template x-for="(teamId, index) in teamOrder.slice(0, 3)" :key="teamId">
                    <p><span class="font-medium" x-text="index + 1"></span>. <span x-text="getTeamById(teamId)?.display_name || getTeamById(teamId)?.team_name"></span></p>
                </template>
            </x-slot:top3>
        </x-prediction-summary>

        <!-- Instructions -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
            <div class="flex items-start space-x-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-medium">How to use:</p>
                    <ul class="mt-1 space-y-1">
                        <li>• Drag constructors up or down to reorder your predictions</li>
                        <li>• Your prediction will be automatically saved</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
