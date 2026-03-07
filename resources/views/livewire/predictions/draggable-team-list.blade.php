<div class="w-full">
    <div
        x-data="{
            teams: @js($teams),
            teamOrder: @js($teamOrder),
            constructorColors: @js(config('constructor_colors')),
            draggedIndex: null,
            draggedOverIndex: null,

            // #region pointer/touch drag (mobile and desktop)
            pointerDragActive: false,
            pointerTeamIndex: null,
            pointerGhost: null,
            pointerThreshold: 10,
            pointerStartX: 0,
            pointerStartY: 0,
            _pointerMoveBound: null,
            _pointerUpBound: null,
            // #endregion

            getConstructorColor(team) {
                if (!team) return null;

                const explicitColor = typeof team === 'object' ? team.color : null;
                if (explicitColor) return explicitColor;

                if (!this.constructorColors) return null;

                const teamName = typeof team === 'object' ? (team.team_name || team.display_name || '') : String(team);
                const normalized = teamName.trim().toLowerCase();
                if (!normalized) return null;

                const exactKey = Object.keys(this.constructorColors).find(key => key.trim().toLowerCase() === normalized);
                if (exactKey) return this.constructorColors[exactKey];

                const bestKey = Object.keys(this.constructorColors)
                    .filter(key => {
                        const candidate = key.trim().toLowerCase();
                        return normalized.includes(candidate) || candidate.includes(normalized);
                    })
                    .sort((a, b) => b.trim().length - a.trim().length)[0];

                return bestKey ? this.constructorColors[bestKey] : null;
            },

            moveTeam(fromIndex, toIndex) {
                if (fromIndex === null || fromIndex === undefined) return;
                if (toIndex < 0 || toIndex >= this.teamOrder.length) return;
                if (fromIndex === toIndex) return;
                const newOrder = [...this.teamOrder];
                const [item] = newOrder.splice(fromIndex, 1);
                newOrder.splice(toIndex, 0, item);
                this.teamOrder = newOrder;
                $wire.updateTeamOrder(newOrder);
            },

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
                    const newOrder = [...this.teamOrder];
                    const [draggedItem] = newOrder.splice(this.draggedIndex, 1);
                    newOrder.splice(dropIndex, 0, draggedItem);
                    this.teamOrder = newOrder;
                    $wire.updateTeamOrder(newOrder);
                }
                this.draggedIndex = null;
                this.draggedOverIndex = null;
            },

            getTeamById(id) {
                return this.teams.find(team => team.id === id);
            },

            // #region pointer/touch drag methods
            pointerDown(e, index) {
                if (e.button !== undefined && e.button !== 0) return;
                if (e.pointerType === 'mouse') return;
                this.pointerTeamIndex = index;
                this.pointerStartX = e.clientX ?? e.touches?.[0]?.clientX ?? 0;
                this.pointerStartY = e.clientY ?? e.touches?.[0]?.clientY ?? 0;
                this.pointerDragActive = false;
                this._pointerMoveBound = (ev) => this.pointerMove(ev);
                this._pointerUpBound = (ev) => this.pointerUp(ev);
                document.addEventListener('pointermove', this._pointerMoveBound, { passive: false });
                document.addEventListener('pointerup', this._pointerUpBound);
                document.addEventListener('pointercancel', this._pointerUpBound);
            },

            pointerMove(e) {
                const x = e.clientX ?? 0;
                const y = e.clientY ?? 0;
                if (!this.pointerDragActive) {
                    const dx = x - this.pointerStartX;
                    const dy = y - this.pointerStartY;
                    if (dx * dx + dy * dy < this.pointerThreshold * this.pointerThreshold) return;
                    this.pointerDragActive = true;
                    this.draggedIndex = this.pointerTeamIndex;
                    this.showGhost(x, y);
                }
                e.preventDefault();
                this.moveGhost(x, y);
                const under = document.elementFromPoint(x, y);
                const teamRow = under?.closest?.('[data-drop-team]');
                if (teamRow) {
                    const idx = teamRow.getAttribute('data-drop-team');
                    if (idx !== null && idx !== '') this.draggedOverIndex = parseInt(idx, 10);
                } else {
                    this.draggedOverIndex = null;
                }
            },

            pointerUp(e) {
                document.removeEventListener('pointermove', this._pointerMoveBound);
                document.removeEventListener('pointerup', this._pointerUpBound);
                document.removeEventListener('pointercancel', this._pointerUpBound);
                this._pointerMoveBound = null;
                this._pointerUpBound = null;
                const x = e.clientX ?? 0;
                const y = e.clientY ?? 0;
                if (this.pointerDragActive && this.pointerTeamIndex !== null) {
                    const under = document.elementFromPoint(x, y);
                    const teamRow = under?.closest?.('[data-drop-team]');
                    if (teamRow) {
                        const dropIndex = parseInt(teamRow.getAttribute('data-drop-team'), 10);
                        if (!isNaN(dropIndex) && dropIndex !== this.pointerTeamIndex) {
                            const newOrder = [...this.teamOrder];
                            const [draggedItem] = newOrder.splice(this.pointerTeamIndex, 1);
                            newOrder.splice(dropIndex, 0, draggedItem);
                            this.teamOrder = newOrder;
                            $wire.updateTeamOrder(newOrder);
                        }
                    }
                }
                this.removeGhost();
                this.pointerDragActive = false;
                this.pointerTeamIndex = null;
                this.draggedIndex = null;
                this.draggedOverIndex = null;
            },

            showGhost(x, y) {
                this.removeGhost();
                const current = this.teamOrder ?? [];
                const teamId = Array.from(current)[this.pointerTeamIndex];
                const team = this.getTeamById(teamId);
                const name = team ? (team.display_name || team.team_name || '') : '';
                const el = document.createElement('div');
                el.setAttribute('data-drag-ghost', '1');
                el.className = 'fixed z-[100] pointer-events-none px-3 py-1.5 rounded border-2 border-blue-400 bg-white dark:bg-zinc-800 shadow-lg text-sm font-medium text-zinc-900 dark:text-zinc-100';
                el.textContent = name;
                el.style.left = (x - 8) + 'px';
                el.style.top = (y - 8) + 'px';
                document.body.appendChild(el);
                this.pointerGhost = el;
            },

            moveGhost(x, y) {
                if (this.pointerGhost) {
                    this.pointerGhost.style.left = (x - 8) + 'px';
                    this.pointerGhost.style.top = (y - 8) + 'px';
                }
            },

            removeGhost() {
                if (this.pointerGhost && this.pointerGhost.parentNode) {
                    this.pointerGhost.parentNode.removeChild(this.pointerGhost);
                }
                this.pointerGhost = null;
            }
            // #endregion
        }"
        class="space-y-2"
    >
        <!-- Team List -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <h4 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</h4>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Constructor order — drag or touch to reorder your predictions.</p>
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
                            'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700': draggedOverIndex === index,
                            'opacity-40': draggedIndex !== null && draggedIndex === index && pointerDragActive
                        }"
                        class="p-4 min-h-[44px] hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-150 touch-none select-none flex items-center"
                        :data-drop-team="index"
                        @pointerdown="pointerDown($event, index)"
                    >
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center space-x-4 min-w-0 cursor-move">
                                <!-- Position Number -->
                                <div class="flex-shrink-0 w-8 h-8 bg-zinc-100 dark:bg-zinc-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300" x-text="index + 1"></span>
                                </div>
                                <!-- Constructor color bar -->
                                <span x-show="getConstructorColor(getTeamById(teamId))" class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[1.25rem]" :style="getConstructorColor(getTeamById(teamId)) ? 'background-color: ' + getConstructorColor(getTeamById(teamId)) : ''" aria-hidden="true"></span>
                                <!-- Team Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100" x-text="getTeamById(teamId)?.display_name || getTeamById(teamId)?.team_name"></span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="getTeamById(teamId)?.driver_surnames || getTeamById(teamId)?.nationality"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-center gap-1 ml-3">
                                <button
                                    type="button"
                                    class="w-6 h-6 flex items-center justify-center rounded-full bg-zinc-800 text-zinc-100 text-xs hover:bg-zinc-700 dark:bg-zinc-200 dark:text-zinc-900 dark:hover:bg-zinc-100"
                                    @click.stop="moveTeam(index, index - 1)"
                                    :disabled="index === 0"
                                >
                                    &#8593;
                                </button>
                                <button
                                    type="button"
                                    class="w-6 h-6 flex items-center justify-center rounded-full bg-zinc-800 text-zinc-100 text-xs hover:bg-zinc-700 dark:bg-zinc-200 dark:text-zinc-900 dark:hover:bg-zinc-100"
                                    @click.stop="moveTeam(index, index + 1)"
                                    :disabled="index === teamOrder.length - 1"
                                >
                                    &#8595;
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
