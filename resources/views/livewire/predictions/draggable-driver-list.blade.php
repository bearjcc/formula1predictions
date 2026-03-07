<div class="w-full">
    @if(!empty($drivers))
        @if($this->isRaceOrderLayout)
            <div
                x-data="{
                    drivers: @js($drivers),
                    driverOrder: @js($driverOrder),
                    fastestLap: @js($fastestLapDriverId),
                    dnfPredictions: @js($dnfPredictions),
                    dnfEligibleFromSlot: @js($this->dnfEligibleFromSlot),
                    predictionType: @js($type),
                    constructorColors: @js(config('constructor_colors')),
                    draggedIndex: null,
                    draggedOverIndex: null,
                    pointerDragActive: false,
                    pointerDriverIndex: null,
                    pointerGhost: null,
                    pointerThreshold: 8,
                    pointerStartX: 0,
                    pointerStartY: 0,
                    _pointerMoveBound: null,
                    _pointerUpBound: null,

                    init() {
                        const fromWire = $wire.get('drivers');
                        if (fromWire && Array.isArray(fromWire) && fromWire.length > 0 && this.drivers.length === 0) {
                            this.drivers = fromWire;
                        }

                        const validIds = this.drivers.map(driver => String(driver.id));
                        const validIdSet = new Set(validIds);
                        const seen = new Set();
                        const nextOrder = [];

                        for (const id of this.driverOrder) {
                            if (id == null || id === '') continue;
                            const normalized = String(id);
                            if (!validIdSet.has(normalized) || seen.has(normalized)) continue;
                            seen.add(normalized);
                            nextOrder.push(id);
                        }

                        for (const driver of this.drivers) {
                            const normalized = String(driver.id);
                            if (seen.has(normalized)) continue;
                            seen.add(normalized);
                            nextOrder.push(driver.id);
                        }

                        this.driverOrder = nextOrder;
                    },

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

                    isDnfEligible(index) {
                        return this.predictionType === 'race' && index >= this.dnfEligibleFromSlot;
                    },

                    hasDnf(driverId) {
                        return driverId && this.dnfPredictions.map(String).includes(String(driverId));
                    },

                    toggleDnf(driverId) {
                        if (!driverId) return;
                        const id = String(driverId);
                        const has = this.dnfPredictions.map(String).includes(id);
                        this.dnfPredictions = has ? this.dnfPredictions.filter(x => String(x) !== id) : [...this.dnfPredictions, id];
                        $wire.toggleDnf(driverId);
                    },

                    setFastestLap(driverId) {
                        this.fastestLap = driverId ?? null;
                        $wire.setFastestLap(this.fastestLap);
                    },

                    getDriverById(id) {
                        return this.drivers.find(driver => String(driver.id) === String(id));
                    },

                    moveDriver(fromIndex, toIndex) {
                        if (fromIndex === null || toIndex === null) return;
                        if (fromIndex === toIndex) return;
                        if (toIndex < 0 || toIndex >= this.driverOrder.length) return;
                        const nextOrder = [...this.driverOrder];
                        const [driverId] = nextOrder.splice(fromIndex, 1);
                        nextOrder.splice(toIndex, 0, driverId);
                        this.driverOrder = nextOrder;
                        $wire.updateDriverOrder(nextOrder);
                    },

                    dragStartRace(e, index) {
                        this.draggedIndex = index;
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', String(index));
                    },

                    dragOverRace(e, index) {
                        e.preventDefault();
                        if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
                        this.draggedOverIndex = index;
                    },

                    dropRace(e, dropIndex) {
                        e.preventDefault();
                        this.moveDriver(this.draggedIndex, dropIndex);
                        this.draggedIndex = null;
                        this.draggedOverIndex = null;
                    },

                    dragEndRace() {
                        this.draggedIndex = null;
                        this.draggedOverIndex = null;
                    },

                    pointerDownRace(e, index) {
                        if (e.button !== undefined && e.button !== 0) return;
                        this.pointerDriverIndex = index;
                        this.pointerStartX = e.clientX ?? 0;
                        this.pointerStartY = e.clientY ?? 0;
                        this.pointerDragActive = false;
                        this._pointerMoveBound = (ev) => this.pointerMoveRace(ev);
                        this._pointerUpBound = () => this.pointerUpRace();
                        document.addEventListener('pointermove', this._pointerMoveBound, { passive: false });
                        document.addEventListener('pointerup', this._pointerUpBound);
                        document.addEventListener('pointercancel', this._pointerUpBound);
                        if (e.pointerType !== 'mouse') e.preventDefault();
                    },

                    pointerMoveRace(e) {
                        const x = e.clientX ?? 0;
                        const y = e.clientY ?? 0;
                        if (!this.pointerDragActive) {
                            const dx = x - this.pointerStartX;
                            const dy = y - this.pointerStartY;
                            if (dx * dx + dy * dy < this.pointerThreshold * this.pointerThreshold) return;
                            this.pointerDragActive = true;
                            this.draggedIndex = this.pointerDriverIndex;
                            this.draggedOverIndex = this.pointerDriverIndex;
                            this.showGhost(x, y);
                        }
                        e.preventDefault();
                        this.moveGhost(x, y);
                        const under = document.elementFromPoint(x, y);
                        const row = under?.closest?.('[data-drop-driver]');
                        if (row) {
                            const idx = row.getAttribute('data-drop-driver');
                            if (idx !== null && idx !== '') this.draggedOverIndex = parseInt(idx, 10);
                        }
                    },

                    pointerUpRace() {
                        document.removeEventListener('pointermove', this._pointerMoveBound);
                        document.removeEventListener('pointerup', this._pointerUpBound);
                        document.removeEventListener('pointercancel', this._pointerUpBound);
                        this._pointerMoveBound = null;
                        this._pointerUpBound = null;
                        if (this.pointerDragActive && this.pointerDriverIndex !== null && this.draggedOverIndex !== null) {
                            this.moveDriver(this.pointerDriverIndex, this.draggedOverIndex);
                        }
                        this.removeGhost();
                        this.pointerDragActive = false;
                        this.pointerDriverIndex = null;
                        this.draggedIndex = null;
                        this.draggedOverIndex = null;
                    },

                    showGhost(x, y) {
                        this.removeGhost();
                        const driverId = this.driverOrder[this.pointerDriverIndex];
                        const driver = this.getDriverById(driverId);
                        const name = driver ? ((driver.name ? driver.name + ' ' : '') + (driver.surname || '')).trim() : '';
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
                }"
                class="space-y-4"
            >
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="font-bold text-zinc-900 dark:text-white">Your prediction (1&ndash;{{ $maxSlots }})</h4>
                            <span wire:loading wire:target="updateDriverOrder" class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 font-medium">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                Saving…
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">All drivers start prefilled in surname order. Scroll using the left gutter or row body, then drag with the handle on the right.</p>
                        @if($type === 'race')
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Positions {{ $this->dnfEligibleFromSlot + 1 }}+ can be marked DNF.</p>
                        @endif
                    </div>

                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        <template x-for="(driverId, index) in driverOrder" :key="driverId">
                            <div
                                x-show="getDriverById(driverId)"
                                :class="{
                                    'bg-blue-50/70 dark:bg-blue-900/20': draggedOverIndex === index,
                                    'opacity-40': draggedIndex !== null && draggedIndex === index
                                }"
                                class="group flex items-stretch gap-2 px-2 sm:px-3 py-1.5 sm:py-2 transition-colors"
                                :data-drop-driver="index"
                                draggable="true"
                                @dragstart="dragStartRace($event, index)"
                                @dragover="dragOverRace($event, index)"
                                @dragleave="draggedOverIndex = null"
                                @drop="dropRace($event, index)"
                                @dragend="dragEndRace()"
                            >
                                <div class="w-10 sm:w-12 shrink-0 flex items-center justify-center rounded-md bg-zinc-50 dark:bg-zinc-900/60 text-xs font-semibold text-zinc-500 dark:text-zinc-400 select-none">
                                    <span x-text="index + 1"></span>
                                </div>

                                <div class="min-w-0 flex-1 flex items-center gap-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 px-3 py-2">
                                    <span x-show="getConstructorColor(getDriverById(driverId)?.team)" class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[1.25rem]" :style="getConstructorColor(getDriverById(driverId)?.team) ? 'background-color: ' + getConstructorColor(getDriverById(driverId)?.team) : ''" aria-hidden="true"></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="font-medium text-sm text-zinc-900 dark:text-zinc-100 truncate" x-text="getDriverById(driverId)?.name + ' ' + getDriverById(driverId)?.surname"></span>
                                            <span class="hidden sm:inline text-[10px] uppercase font-semibold tracking-wider text-zinc-400" x-text="getDriverById(driverId)?.nationality?.substring(0, 3)"></span>
                                        </div>
                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate" x-text="getDriverById(driverId)?.team?.display_name || getDriverById(driverId)?.team?.team_name || 'Individual Entry'"></div>
                                    </div>
                                    <template x-if="isDnfEligible(index)">
                                        <button
                                            type="button"
                                            @click.stop="toggleDnf(driverId)"
                                            @pointerdown.stop
                                            :class="hasDnf(driverId) ? 'bg-zinc-200 dark:bg-zinc-300 text-red-600 dark:text-red-500' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-500'"
                                            class="flex-shrink-0 rounded-full px-2.5 text-[10px] font-bold"
                                        >
                                            DNF
                                        </button>
                                    </template>
                                    <button
                                        type="button"
                                        class="drag-handle flex-shrink-0 rounded-md border border-zinc-200 dark:border-zinc-600 text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-500 touch-none"
                                        @pointerdown.stop="pointerDownRace($event, index)"
                                        :aria-label="'Drag ' + (getDriverById(driverId)?.surname || getDriverById(driverId)?.name || 'driver')"
                                        title="Drag to reorder"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h.01M9 12h.01M9 19h.01M15 5h.01M15 12h.01M15 19h.01"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <p class="text-xs uppercase font-bold text-zinc-400 tracking-widest mb-2">Fastest Lap</p>
                        <select
                            :value="fastestLap"
                            @change="setFastestLap($event.target.value || null)"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 px-3 py-2 text-sm"
                        >
                            <option value="">No driver selected</option>
                            <template x-for="driver in drivers" :key="driver.id">
                                <option :value="driver.id" x-text="driver.name + ' ' + driver.surname"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>
        @else
            <div
                x-data="{
                    drivers: @js($drivers),
                    driverOrder: @js($driverOrder),
                    fastestLap: @js($fastestLapDriverId),
                    constructorColors: @js(config('constructor_colors')),
                    draggedIndex: null,
                    draggedOverIndex: null,
                    pointerDragActive: false,
                    pointerDriverIndex: null,
                    pointerGhost: null,
                    pointerThreshold: 8,
                    pointerStartX: 0,
                    pointerStartY: 0,
                    _pointerMoveBound: null,
                    _pointerUpBound: null,

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
                    },

                    pointerDown(e, index) {
                        if (e.button !== undefined && e.button !== 0) return;
                        this.pointerDriverIndex = index;
                        this.pointerStartX = e.clientX ?? 0;
                        this.pointerStartY = e.clientY ?? 0;
                        this.pointerDragActive = false;
                        this._pointerMoveBound = (ev) => this.pointerMove(ev);
                        this._pointerUpBound = () => this.pointerUp();
                        document.addEventListener('pointermove', this._pointerMoveBound, { passive: false });
                        document.addEventListener('pointerup', this._pointerUpBound);
                        document.addEventListener('pointercancel', this._pointerUpBound);
                        if (e.pointerType !== 'mouse') e.preventDefault();
                    },

                    pointerMove(e) {
                        const x = e.clientX ?? 0;
                        const y = e.clientY ?? 0;
                        if (!this.pointerDragActive) {
                            const dx = x - this.pointerStartX;
                            const dy = y - this.pointerStartY;
                            if (dx * dx + dy * dy < this.pointerThreshold * this.pointerThreshold) return;
                            this.pointerDragActive = true;
                            this.draggedIndex = this.pointerDriverIndex;
                            this.draggedOverIndex = this.pointerDriverIndex;
                            this.showGhost(x, y);
                        }
                        e.preventDefault();
                        this.moveGhost(x, y);
                        const under = document.elementFromPoint(x, y);
                        const driverRow = under?.closest?.('[data-drop-driver]');
                        if (driverRow) {
                            const idx = driverRow.getAttribute('data-drop-driver');
                            if (idx !== null && idx !== '') this.draggedOverIndex = parseInt(idx, 10);
                        }
                    },

                    pointerUp() {
                        document.removeEventListener('pointermove', this._pointerMoveBound);
                        document.removeEventListener('pointerup', this._pointerUpBound);
                        document.removeEventListener('pointercancel', this._pointerUpBound);
                        this._pointerMoveBound = null;
                        this._pointerUpBound = null;
                        if (this.pointerDragActive && this.pointerDriverIndex !== null && this.draggedOverIndex !== null && this.draggedOverIndex !== this.pointerDriverIndex) {
                            const newOrder = [...this.driverOrder];
                            const [draggedItem] = newOrder.splice(this.pointerDriverIndex, 1);
                            newOrder.splice(this.draggedOverIndex, 0, draggedItem);
                            this.driverOrder = newOrder;
                            $wire.updateDriverOrder(newOrder);
                        }
                        this.removeGhost();
                        this.pointerDragActive = false;
                        this.pointerDriverIndex = null;
                        this.draggedIndex = null;
                        this.draggedOverIndex = null;
                    },

                    showGhost(x, y) {
                        this.removeGhost();
                        const driverId = this.driverOrder[this.pointerDriverIndex];
                        const driver = this.getDriverById(driverId);
                        const name = driver ? ((driver.name ? driver.name + ' ' : '') + (driver.surname || '')).trim() : '';
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
                }"
                class="space-y-4"
            >
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-zinc-900 dark:text-white">{{ $raceName }} Predicted Order</h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Scroll with the row body. Drag only from the handle on the right.</p>
                        </div>
                        <span wire:loading wire:target="updateDriverOrder" class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 font-medium">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            Saving…
                        </span>
                    </div>

                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        <template x-for="(driverId, index) in driverOrder" :key="driverId">
                            <div
                                x-show="getDriverById(driverId)"
                                :class="{
                                    'bg-blue-50/50 dark:bg-blue-900/10': draggedOverIndex === index,
                                    'opacity-40': draggedIndex !== null && draggedIndex === index
                                }"
                                class="group flex items-stretch gap-2 px-2 sm:px-3 py-2 transition-colors"
                                :data-drop-driver="index"
                                draggable="true"
                                @dragstart="dragStart(index)"
                                @dragover="dragOver($event, index)"
                                @dragleave="draggedOverIndex = null"
                                @drop="drop($event, index)"
                                @dragend="dragEnd()"
                            >
                                <div class="w-10 sm:w-12 shrink-0 flex items-center justify-center rounded-md bg-zinc-50 dark:bg-zinc-900/60 text-xs font-semibold text-zinc-500 dark:text-zinc-400 select-none">
                                    <span x-text="index + 1"></span>
                                </div>
                                <div class="min-w-0 flex-1 flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 px-3 py-2">
                                    <span x-show="getConstructorColor(getDriverById(driverId)?.team)" class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[1.25rem]" :style="getConstructorColor(getDriverById(driverId)?.team) ? 'background-color: ' + getConstructorColor(getDriverById(driverId)?.team) : ''" aria-hidden="true"></span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 truncate" x-text="getDriverById(driverId)?.name + ' ' + getDriverById(driverId)?.surname"></span>
                                            <span class="text-[10px] uppercase font-semibold text-zinc-400 tracking-wider hidden sm:inline" x-text="getDriverById(driverId)?.nationality?.substring(0, 3)"></span>
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium truncate" x-text="getDriverById(driverId)?.team?.display_name || getDriverById(driverId)?.team?.team_name || 'Individual Entry'"></div>
                                    </div>
                                    <button
                                        type="button"
                                        class="drag-handle flex-shrink-0 rounded-md border border-zinc-200 dark:border-zinc-600 text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-500 touch-none"
                                        @pointerdown.stop="pointerDown($event, index)"
                                        :aria-label="'Drag ' + (getDriverById(driverId)?.surname || getDriverById(driverId)?.name || 'driver')"
                                        title="Drag to reorder"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h.01M9 12h.01M9 19h.01M15 5h.01M15 12h.01M15 19h.01"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-900 rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
            <x-mary-icon name="o-user-group" class="w-12 h-12 text-zinc-300 mx-auto mb-3" />
            <p class="text-zinc-500">No active drivers found for this prediction.</p>
        </div>
    @endif
</div>
