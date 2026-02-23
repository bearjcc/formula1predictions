<x-layouts.layout title="How Scoring Works" headerSubtitle="Points per position accuracy and bonuses.">
    <div class="max-w-4xl mx-auto py-8">
        <p class="text-auto-muted mb-8">
            We compare your picks to official results. Each predicted position earns points by how close it is (diff 0 = exact); bonuses for fastest lap, DNFs, and perfect picks. Race, sprint, and season prediction scores sum to your leaderboard total.
        </p>

        <x-mary-card class="mb-8">
            <h2 class="text-heading-2 mb-4">Race (full)</h2>
            <p class="text-sm text-auto-muted mb-3">Points per position difference (predicted vs actual):</p>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm border border-zinc-200 dark:border-zinc-600 rounded-lg overflow-hidden">
                    <thead class="bg-zinc-100 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Diff</th>
                            <th class="px-3 py-2 text-left font-semibold">Points</th>
                            <th class="px-3 py-2 text-left font-semibold">Diff</th>
                            <th class="px-3 py-2 text-left font-semibold">Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-600 text-auto-muted">
                        <tr><td class="px-3 py-2">0</td><td class="px-3 py-2">+25</td><td class="px-3 py-2">10</td><td class="px-3 py-2">0</td></tr>
                        <tr><td class="px-3 py-2">1</td><td class="px-3 py-2">+18</td><td class="px-3 py-2">11</td><td class="px-3 py-2">-1</td></tr>
                        <tr><td class="px-3 py-2">2</td><td class="px-3 py-2">+15</td><td class="px-3 py-2">12</td><td class="px-3 py-2">-2</td></tr>
                        <tr><td class="px-3 py-2">3</td><td class="px-3 py-2">+12</td><td class="px-3 py-2">13</td><td class="px-3 py-2">-4</td></tr>
                        <tr><td class="px-3 py-2">4</td><td class="px-3 py-2">+10</td><td class="px-3 py-2">14</td><td class="px-3 py-2">-6</td></tr>
                        <tr><td class="px-3 py-2">5</td><td class="px-3 py-2">+8</td><td class="px-3 py-2">15</td><td class="px-3 py-2">-8</td></tr>
                        <tr><td class="px-3 py-2">6</td><td class="px-3 py-2">+6</td><td class="px-3 py-2">16</td><td class="px-3 py-2">-10</td></tr>
                        <tr><td class="px-3 py-2">7</td><td class="px-3 py-2">+4</td><td class="px-3 py-2">17</td><td class="px-3 py-2">-12</td></tr>
                        <tr><td class="px-3 py-2">8</td><td class="px-3 py-2">+2</td><td class="px-3 py-2">18</td><td class="px-3 py-2">-15</td></tr>
                        <tr><td class="px-3 py-2">9</td><td class="px-3 py-2">+1</td><td class="px-3 py-2">19</td><td class="px-3 py-2">-18</td></tr>
                        <tr><td class="px-3 py-2"></td><td class="px-3 py-2"></td><td class="px-3 py-2">20+</td><td class="px-3 py-2">-25</td></tr>
                    </tbody>
                </table>
            </div>
            <ul class="text-sm text-auto-muted space-y-1 list-disc list-inside">
                <li>Fastest lap: +10. DNF wager: +10 correct, -10 wrong (optional).</li>
                <li>Perfect prediction (all positions exact): +50.</li>
                <li>Half points (FIA shortened race): race score halved (rounded).</li>
                <li>Partial predictions: only filled positions are scored. DNS/DSQ/etc.: that driver = 0.</li>
            </ul>
        </x-mary-card>

        <x-mary-card class="mb-8">
            <h2 class="text-heading-2 mb-4">Sprint (top 8 only)</h2>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm border border-zinc-200 dark:border-zinc-600 rounded-lg overflow-hidden max-w-xs">
                    <thead class="bg-zinc-100 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Diff</th>
                            <th class="px-3 py-2 text-left font-semibold">Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-600 text-auto-muted">
                        @foreach([0=>8, 1=>7, 2=>6, 3=>5, 4=>4, 5=>3, 6=>2, 7=>1] as $d => $pts)
                        <tr><td class="px-3 py-2">{{ $d }}</td><td class="px-3 py-2">{{ $pts }}</td></tr>
                        @endforeach
                        <tr><td class="px-3 py-2">8+</td><td class="px-3 py-2">0</td></tr>
                    </tbody>
                </table>
            </div>
            <ul class="text-sm text-auto-muted space-y-1 list-disc list-inside">
                <li>Fastest lap: +5. Perfect top 8: +15.</li>
            </ul>
        </x-mary-card>

        <x-mary-card class="mb-8">
            <h2 class="text-heading-2 mb-4">Season (preseason &amp; midseason)</h2>
            <p class="text-sm text-auto-muted mb-3">Preseason is due at the same time as the first race prediction (1 hour before first qualifying).</p>
            <h3 class="text-lg font-semibold mb-2">Preseason</h3>
            <p class="text-sm text-auto-muted mb-2">Constructor championship order (position diff vs final standings):</p>
            <div class="overflow-x-auto mb-4 max-w-xs">
                <table class="w-full text-sm border border-zinc-200 dark:border-zinc-600 rounded-lg overflow-hidden">
                    <thead class="bg-zinc-100 dark:bg-zinc-800">
                        <tr><th class="px-3 py-2 text-left font-semibold">Diff</th><th class="px-3 py-2 text-left font-semibold">Points</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-600 text-auto-muted">
                        @foreach([0=>10, 1=>8, 2=>6, 3=>4, 4=>2, 5=>0, 6=>-2, 7=>-4, 8=>-6, 9=>-8, 10=>-10] as $d => $pts)
                        <tr><td class="px-3 py-2">{{ $d }}</td><td class="px-3 py-2">{{ $pts }}</td></tr>
                        @endforeach
                        <tr><td class="px-3 py-2">10+</td><td class="px-3 py-2">-10</td></tr>
                    </tbody>
                </table>
            </div>
            <ul class="text-sm text-auto-muted space-y-1 list-disc list-inside mb-4">
                <li>Teammate battles: for each constructor, pick who finishes higher in the championship. +5 per correct.</li>
                <li>Red flags (season total) and safety cars (season total): points by prediction error: diff 0 = 15, diff 1 = 10, diff 2 = 5, diff 3+ = 0. Actuals set by admin at season end.</li>
            </ul>
            <h3 class="text-lg font-semibold mb-2">Midseason</h3>
            <p class="text-sm text-auto-muted mb-2">Driver and constructor championship order vs final standings. Same diff table as race (0→25 … 10→0, 11+ negative). Perfect season (every driver and constructor exact): +50.</p>
        </x-mary-card>

        <x-mary-card>
            <h2 class="text-heading-2 mb-2">Other</h2>
            <p class="text-sm text-auto-muted">Cancelled race: predictions scored 0. Result changes (e.g. FIA penalties) can be rescored by admins.</p>
        </x-mary-card>
    </div>
</x-layouts.layout>
