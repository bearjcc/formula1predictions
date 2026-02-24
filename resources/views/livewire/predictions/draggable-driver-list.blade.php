<div class="w-full">
    @if(!empty($drivers))
    @if($this->isRaceOrderLayout)
    {{-- Two-column: left = slots 1 to maxSlots, right = driver pool. Drag from pool or reorder in slots. --}}
    <div
        x-data="{
            drivers: @js($drivers),
            driverOrder: @js($driverOrder),
            fastestLap: @js($fastestLapDriverId),
            maxSlots: @js($maxSlots),
            dnfPredictions: @js($dnfPredictions),
            dnfEligibleFromSlot: @js($this->dnfEligibleFromSlot),
            predictionType: @js($type),
            constructorColors: @js(config('constructor_colors')),
            draggedDriverId: null,
            draggedFromIndex: null,
            dragOverIndex: null,

            getConstructorColor(teamName) {
                if (!teamName || !this.constructorColors) return null;
                const k = Object.keys(this.constructorColors).find(key => key.trim().toLowerCase() === String(teamName).trim().toLowerCase());
                return k ? this.constructorColors[k] : null;
            },

            init() {
                const fromWire = $wire.get('drivers');
                if (fromWire && Array.isArray(fromWire) && fromWire.length > 0 && this.drivers.length === 0) {
                    this.drivers = fromWire;
                }
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

            get availableDrivers() {
                const placed = new Set(this.driverOrder.filter(id => id != null).map(id => String(id)));
                const lastWord = (d) => { const s = (d.surname || d.name || '').trim(); const w = s.split(/\s+/).filter(Boolean); return w.length ? w[w.length - 1] : s; };
                return this.drivers
                    .filter(d => !placed.has(String(d.id)))
                    .sort((a, b) => lastWord(a).localeCompare(lastWord(b), 'en', { sensitivity: 'base' }));
            },

            getDriverById(id) {
                return this.drivers.find(d => String(d.id) === String(id));
            },

            insertDriverAt(driverId, dropIndex) {
                if (driverId == null || dropIndex < 0 || dropIndex >= this.maxSlots) return;
                const max = this.maxSlots;
                const slots = Array.from({ length: max }, (_, i) => this.driverOrder[i] ?? null);
                for (let i = 0; i < max; i++) {
                    if (slots[i] != null && String(slots[i]) === String(driverId)) slots[i] = null;
                }
                slots[dropIndex] = driverId;
                this.driverOrder = slots;
                $wire.updateDriverOrder(this.driverOrder);
            },

            dragStartRace(e, driverId, from, fromIndex) {
                this.draggedDriverId = driverId;
                this.draggedFromIndex = from === 'slot' ? fromIndex : null;
                e.dataTransfer.setData('application/json', JSON.stringify({ driverId, from, fromIndex: fromIndex ?? null }));
                e.dataTransfer.effectAllowed = 'move';
            },

            dragOverRace(e, index) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                this.dragOverIndex = index;
            },

            dragLeaveRace() {
                this.dragOverIndex = null;
            },

            dropRace(e, dropIndex) {
                e.preventDefault();
                e.stopPropagation();
                this.dragOverIndex = null;
                let driverId = null;
                try {
                    const data = JSON.parse(e.dataTransfer.getData('application/json') || '{}');
                    if (data.driverId != null) driverId = data.driverId;
                } catch (_) {}
                if (driverId == null) driverId = this.draggedDriverId;
                if (driverId == null) return;
                this.insertDriverAt(driverId, dropIndex);
                this.draggedDriverId = null;
                this.draggedFromIndex = null;
            },

            dragEndRace() {
                this.draggedDriverId = null;
                this.draggedFromIndex = null;
                this.dragOverIndex = null;
            },

            setFastestLap(driverId) {
                this.fastestLap = driverId ?? null;
                $wire.setFastestLap(this.fastestLap);
            },

            slotDriverId(index) {
                return this.driverOrder[index] ?? null;
            },

            // #region pointer/touch drag (mobile and desktop fallback)
            pointerDragActive: false,
            pointerDriverId: null,
            pointerFrom: null,
            pointerFromIndex: null,
            pointerGhost: null,
            pointerThreshold: 10,
            pointerStartX: 0,
            pointerStartY: 0,
            _pointerMoveBound: null,
            _pointerUpBound: null,
            justDragged: false,

            firstEmptySlot() {
                for (let i = 0; i < this.maxSlots; i++) {
                    if (this.driverOrder[i] == null || this.driverOrder[i] === '') return i;
                }
                return -1;
            },

            fillFirstEmpty(driverId) {
                const idx = this.firstEmptySlot();
                if (idx >= 0 && driverId != null) this.insertDriverAt(driverId, idx);
            },

            pointerDownRace(e, driverId, from, fromIndex) {
                if (e.button !== undefined && e.button !== 0) return;
                this.justDragged = false;
                this.pointerDriverId = driverId;
                this.pointerFrom = from;
                this.pointerFromIndex = fromIndex;
                this.pointerStartX = e.clientX ?? e.touches?.[0]?.clientX ?? 0;
                this.pointerStartY = e.clientY ?? e.touches?.[0]?.clientY ?? 0;
                this.pointerDragActive = false;
                this._pointerMoveBound = (ev) => this.pointerMoveRace(ev);
                this._pointerUpBound = (ev) => this.pointerUpRace(ev);
                document.addEventListener('pointermove', this._pointerMoveBound, { passive: false });
                document.addEventListener('pointerup', this._pointerUpBound);
                document.addEventListener('pointercancel', this._pointerUpBound);
            },

            pointerMoveRace(e) {
                const x = e.clientX ?? 0;
                const y = e.clientY ?? 0;
                if (!this.pointerDragActive) {
                    const dx = x - this.pointerStartX;
                    const dy = y - this.pointerStartY;
                    if (dx * dx + dy * dy < this.pointerThreshold * this.pointerThreshold) return;
                    this.pointerDragActive = true;
                    this.draggedDriverId = this.pointerDriverId;
                    this.draggedFromIndex = this.pointerFrom === 'slot' ? this.pointerFromIndex : null;
                    this.showGhost(x, y);
                }
                e.preventDefault();
                this.moveGhost(x, y);
                const under = document.elementFromPoint(x, y);
                const slotRow = under?.closest?.('[data-drop-slot]');
                if (slotRow) {
                    const idx = slotRow.getAttribute('data-drop-slot');
                    if (idx !== null && idx !== '') this.dragOverIndex = parseInt(idx, 10);
                } else {
                    this.dragOverIndex = null;
                }
            },

            pointerUpRace(e) {
                document.removeEventListener('pointermove', this._pointerMoveBound);
                document.removeEventListener('pointerup', this._pointerUpBound);
                document.removeEventListener('pointercancel', this._pointerUpBound);
                this._pointerMoveBound = null;
                this._pointerUpBound = null;
                const x = e.clientX ?? 0;
                const y = e.clientY ?? 0;
                if (this.pointerDragActive && this.pointerDriverId != null) {
                    const under = document.elementFromPoint(x, y);
                    const slotRow = under?.closest?.('[data-drop-slot]');
                    if (slotRow) {
                        const idx = parseInt(slotRow.getAttribute('data-drop-slot'), 10);
                        if (!isNaN(idx) && idx >= 0 && idx < this.maxSlots) {
                            this.insertDriverAt(this.pointerDriverId, idx);
                            this.justDragged = true;
                        }
                    }
                }
                this.removeGhost();
                this.pointerDragActive = false;
                this.pointerDriverId = null;
                this.pointerFrom = null;
                this.pointerFromIndex = null;
                this.draggedDriverId = null;
                this.draggedFromIndex = null;
                this.dragOverIndex = null;
            },

            showGhost(x, y) {
                this.removeGhost();
                const driver = this.getDriverById(this.pointerDriverId);
                const name = driver ? (driver.surname || (driver.name + ' ' + (driver.surname || '')).trim()) : '';
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
        class="space-y-4"
    >
        {{-- Mobile: constructors-style list - position markers left (outside draggable), drag to sort; unplaced alphabetically --}}
        <div class="md:hidden space-y-2">
            <div class="px-1 py-1">
                <h4 class="font-bold text-zinc-900 dark:text-white text-sm">Your prediction (1&ndash;{{ $maxSlots }})</h4>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Drag to reorder. Tap unplaced to fill first empty. Positions {{ $this->dnfEligibleFromSlot + 1 }}+ can be marked DNF.</p>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700 max-h-[50vh] overflow-y-auto">
                    <template x-for="(_, index) in Array.from({ length: maxSlots }, (_, i) => i)" :key="index">
                        <div
                            :class="{
                                'bg-blue-50/50 dark:bg-blue-900/10': dragOverIndex === index,
                                'opacity-40': draggedDriverId && draggedFromIndex === index
                            }"
                            class="flex items-center gap-2 py-1.5 px-2 min-h-[44px] border-zinc-100 dark:border-zinc-700/50 transition-colors"
                            :data-drop-slot="index"
                            @dragover.prevent="dragOverRace($event, index)"
                            @dragleave="dragLeaveRace()"
                            @drop.prevent="dropRace($event, index)"
                        >
                            {{-- Position marker: left, outside draggable area --}}
                            <div class="flex-shrink-0 w-7 flex items-center justify-center">
                                <span class="text-zinc-500 dark:text-zinc-400 text-xs font-semibold" x-text="index + 1"></span>
                            </div>
                            <template x-if="slotDriverId(index)">
                                <div
                                    class="flex-1 flex items-center gap-1.5 cursor-move select-none rounded border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800/80 py-1 px-2 min-w-0"
                                    draggable="true"
                                    @dragstart="dragStartRace($event, slotDriverId(index), 'slot', index)"
                                    @dragend="dragEndRace()"
                                    @pointerdown="pointerDownRace($event, slotDriverId(index), 'slot', index)"
                                    :title="getDriverById(slotDriverId(index)) ? (getDriverById(slotDriverId(index)).name + ' ' + getDriverById(slotDriverId(index)).surname) : ''"
                                >
                                    <span x-show="getConstructorColor(getDriverById(slotDriverId(index))?.team?.team_name)" class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[1rem]" :style="getConstructorColor(getDriverById(slotDriverId(index))?.team?.team_name) ? 'background-color: ' + getConstructorColor(getDriverById(slotDriverId(index))?.team?.team_name) : ''" aria-hidden="true"></span>
                                    <span class="flex-shrink-0 text-zinc-400 dark:text-zinc-500 w-3.5" aria-hidden="true">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                    </span>
                                    <span class="flex-1 min-w-0 font-medium text-zinc-900 dark:text-zinc-100 text-sm truncate" x-text="getDriverById(slotDriverId(index))?.surname || (getDriverById(slotDriverId(index))?.name + ' ' + getDriverById(slotDriverId(index))?.surname)"></span>
                                    <template x-if="isDnfEligible(index)">
                                        <button
                                            type="button"
                                            @click.stop="toggleDnf(slotDriverId(index))"
                                            :class="hasDnf(slotDriverId(index)) ? 'bg-zinc-200 dark:bg-zinc-300 text-red-600 dark:text-red-500' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-500'"
                                            class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-bold"
                                        >DNF</button>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!slotDriverId(index)">
                                <div
                                    class="flex-1 rounded border border-dashed border-zinc-300 dark:border-zinc-600 py-1 px-2 text-zinc-400 dark:text-zinc-500 text-xs italic min-h-[28px] flex items-center"
                                    :data-drop-slot="index"
                                    @dragover.prevent="dragOverRace($event, index)"
                                    @dragleave="dragLeaveRace()"
                                    @drop.prevent="dropRace($event, index)"
                                >Drop here</div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            <div class="px-1 py-1">
                <h4 class="font-bold text-zinc-900 dark:text-white text-sm" x-text="'Unplaced (' + (availableDrivers?.length ?? 0) + ') - tap to fill'"></h4>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-1.5 flex flex-wrap gap-1.5 max-h-[30vh] overflow-y-auto">
                    <template x-for="driver in availableDrivers" :key="driver.id">
                        <button
                            type="button"
                            class="flex items-center gap-1.5 cursor-move select-none rounded border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800/80 py-1.5 px-2 hover:border-zinc-300 dark:hover:border-zinc-500 active:bg-zinc-100 dark:active:bg-zinc-700 text-sm font-medium text-zinc-900 dark:text-zinc-100"
                            draggable="true"
                            @dragstart="dragStartRace($event, driver.id, 'pool', null)"
                            @dragend="dragEndRace()"
                            @pointerdown="pointerDownRace($event, driver.id, 'pool', null)"
                            @click.prevent="if (!justDragged) fillFirstEmpty(driver.id); justDragged = false"
                            :title="driver.name + ' ' + driver.surname"
                        >
                            <span x-show="getConstructorColor(driver.team?.team_name)" class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[0.875rem]" :style="getConstructorColor(driver.team?.team_name) ? 'background-color: ' + getConstructorColor(driver.team?.team_name) : ''" aria-hidden="true"></span>
                            <span class="flex-shrink-0 text-zinc-400 dark:text-zinc-500 w-3.5" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                            </span>
                            <span x-text="driver.surname || (driver.name + ' ' + driver.surname)"></span>
                        </button>
                    </template>
                </div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-2">
                <p class="text-[10px] uppercase font-bold text-zinc-400 tracking-widest mb-1">Fastest Lap</p>
                <select
                    :value="fastestLap"
                    @change="setFastestLap($event.target.value || null)"
                    class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 px-2 py-1.5 text-sm"
                >
                    <option value="">None</option>
                    <template x-for="driver in drivers" :key="driver.id">
                        <option :value="driver.id" x-text="driver.surname || (driver.name + ' ' + driver.surname)"></option>
                    </template>
                </select>
            </div>
        </div>

        {{-- Desktop: two-column layout (unchanged behavior, add pointer handlers for touch devices) --}}
        <div class="hidden md:grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
            <section class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden min-h-[400px] flex flex-col">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                    <h4 class="font-bold text-zinc-900 dark:text-white">Your prediction (1&ndash;{{ $maxSlots }})</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Drag drivers from the right or reorder here. Positions outside points ({{ $this->dnfEligibleFromSlot + 1 }}+) can be marked DNF.</p>
                </div>
                <div class="flex-1 divide-y divide-zinc-200 dark:divide-zinc-700 overflow-y-auto">
                    <template x-for="(_, index) in Array.from({ length: maxSlots }, (_, i) => i)" :key="index">
                        <div
                            :class="{
                                'bg-blue-50/50 dark:bg-blue-900/10': dragOverIndex === index,
                                'opacity-40': draggedDriverId && draggedFromIndex === index
                            }"
                            class="flex items-center gap-2 p-2 sm:p-3 min-h-[52px] border-zinc-100 dark:border-zinc-700/50 transition-colors"
                            :data-drop-slot="index"
                            @dragover.prevent="dragOverRace($event, index)"
                            @dragleave="dragLeaveRace()"
                            @drop.prevent="dropRace($event, index)"
                        >
                            <span class="flex-shrink-0 w-7 text-zinc-500 dark:text-zinc-400 text-sm font-medium" x-text="index + 1 + '.'"></span>
                            <template x-if="slotDriverId(index)">
                                <div
                                    class="flex-1 flex items-center gap-2 cursor-move select-none group rounded border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800/80 py-1.5 px-2 min-w-0"
                                    draggable="true"
                                    @dragstart="dragStartRace($event, slotDriverId(index), 'slot', index)"
                                    @dragend="dragEndRace()"
                                    @pointerdown="pointerDownRace($event, slotDriverId(index), 'slot', index)"
                                >
                                    <span x-show="getConstructorColor(getDriverById(slotDriverId(index))?.team?.team_name)" class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[1rem]" :style="getConstructorColor(getDriverById(slotDriverId(index))?.team?.team_name) ? 'background-color: ' + getConstructorColor(getDriverById(slotDriverId(index))?.team?.team_name) : ''" aria-hidden="true"></span>
                                    <span class="flex-shrink-0 text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600" aria-hidden="true" title="Drag to reorder">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                    </span>
                                    <span class="flex-1 min-w-0 font-medium text-zinc-900 dark:text-zinc-100 truncate" x-text="getDriverById(slotDriverId(index))?.name + ' ' + getDriverById(slotDriverId(index))?.surname"></span>
                                    <span class="text-[10px] uppercase text-zinc-400 flex-shrink-0" x-text="getDriverById(slotDriverId(index))?.team?.display_name || getDriverById(slotDriverId(index))?.team?.team_name || ''"></span>
                                </div>
                            </template>
                            <template x-if="isDnfEligible(index) && slotDriverId(index)">
                                <button
                                    type="button"
                                    @click="toggleDnf(slotDriverId(index))"
                                    :class="hasDnf(slotDriverId(index)) ? 'bg-zinc-200 dark:bg-zinc-300 text-red-600 dark:text-red-500' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-500'"
                                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-[10px] font-bold cursor-pointer"
                                    :title="hasDnf(slotDriverId(index)) ? 'Predicted DNF' : 'Predict DNF'"
                                >
                                    DNF
                                </button>
                            </template>
                            <template x-if="!slotDriverId(index)">
                                <div
                                    class="flex-1 flex items-center rounded border border-dashed border-zinc-300 dark:border-zinc-600 py-2 px-3 text-zinc-400 dark:text-zinc-500 text-sm italic min-h-[2.5rem]"
                                    :data-drop-slot="index"
                                    @dragover.prevent="dragOverRace($event, index)"
                                    @dragleave="dragLeaveRace()"
                                    @drop.prevent="dropRace($event, index)"
                                >
                                    Drop here
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </section>
            <section class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden min-h-[400px] flex flex-col">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                    <h4 class="font-bold text-zinc-900 dark:text-white">Drivers (drag into list)</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Drag a driver into a slot on the left.</p>
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-1.5">
                    <template x-for="driver in availableDrivers" :key="driver.id">
                        <div
                            class="flex items-center gap-2 cursor-move select-none rounded border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800/80 py-2 px-3 hover:border-zinc-300 dark:hover:border-zinc-500 transition-colors"
                            draggable="true"
                            @dragstart="dragStartRace($event, driver.id, 'pool', null)"
                            @dragend="dragEndRace()"
                            @pointerdown="pointerDownRace($event, driver.id, 'pool', null)"
                        >
                            <span x-show="getConstructorColor(driver.team?.team_name)" class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[1rem]" :style="getConstructorColor(driver.team?.team_name) ? 'background-color: ' + getConstructorColor(driver.team?.team_name) : ''" aria-hidden="true"></span>
                            <span class="flex-shrink-0 text-zinc-400 dark:text-zinc-500" title="Drag into prediction list">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                            </span>
                            <span class="flex-1 min-w-0 font-medium text-zinc-900 dark:text-zinc-100" x-text="driver.name + ' ' + driver.surname"></span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="driver.team?.display_name || driver.team?.team_name || ''"></span>
                        </div>
                    </template>
                </div>
            </section>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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
    {{-- Single-list layout for championship order (preseason/midseason) --}}
    <div
        x-data="{
            drivers: @js($drivers),
            driverOrder: @js($driverOrder),
            fastestLap: @js($fastestLapDriverId),
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
                            <div class="flex-shrink-0 text-zinc-300 dark:text-zinc-600 group-hover:text-zinc-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                            </div>
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center font-bold"
                                 :class="index === 0 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400' : (index === 1 ? 'bg-slate-200 text-slate-700 dark:bg-slate-700/60 dark:text-slate-300' : (index === 2 ? 'bg-amber-100 text-amber-700 dark:bg-amber-800/20 dark:text-amber-500' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500'))">
                                <span x-text="index + 1"></span>
                            </div>
                            <span x-show="getConstructorColor(getDriverById(driverId)?.team?.team_name)" class="flex-shrink-0 w-1 rounded-full self-stretch min-h-[1.25rem]" :style="getConstructorColor(getDriverById(driverId)?.team?.team_name) ? 'background-color: ' + getConstructorColor(getDriverById(driverId)?.team?.team_name) : ''" aria-hidden="true"></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 truncate" x-text="getDriverById(driverId)?.name + ' ' + getDriverById(driverId)?.surname"></span>
                                    <span class="text-[10px] uppercase font-semibold text-zinc-400 tracking-wider" x-text="getDriverById(driverId)?.nationality?.substring(0, 3)"></span>
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium" x-text="getDriverById(driverId)?.team?.display_name || getDriverById(driverId)?.team?.team_name || 'Individual Entry'"></div>
                            </div>
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
