<x-layouts.layout title="How Scoring Works" headerSubtitle="How your race predictions are turned into points and feed into the season leaderboard.">
    <div class="max-w-4xl mx-auto py-8">
        <x-mary-card class="mb-8">
            <h2 class="text-heading-2 mb-3">Big picture</h2>
            <p class="text-auto-muted mb-3">
                For each prediction we compare what you picked against the official race results. You earn points when you are
                close to the real finishing order, and lose a few points when you are way off. Extra bonuses apply for
                fastest lap, DNFs, perfect predictions, and championship (preseason / midseason) picks.
            </p>
            <p class="text-auto-muted">
                All of your prediction scores (race, sprint, preseason, midseason) are added together to produce your total
                season score, which is what the leaderboard uses.
            </p>
        </x-mary-card>

        <x-mary-card class="mb-8">
            <h2 class="text-heading-2 mb-3">Race predictions (full race)</h2>
            <p class="text-auto-muted mb-4">
                For normal race predictions we look at how far away each predicted driver is from their real finishing
                position. A perfect match (diff 0) earns the most points; being a few places off earns fewer (or zero)
                points; being very far off can give small negative points.
            </p>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <h3 class="font-semibold mb-2 text-sm tracking-wide uppercase text-auto-muted">Position accuracy</h3>
                    <p class="text-auto-muted mb-2">
                        The scoring uses a fixed table based on how many positions away you are from the real result:
                    </p>
                    <ul class="list-disc list-inside text-sm text-auto-muted space-y-1">
                        <li><span class="font-semibold">Exactly right (diff 0)</span>: +25 points</li>
                        <li><span class="font-semibold">Close (diff 1–2)</span>: +18, +15 points</li>
                        <li><span class="font-semibold">Still decent (diff 3–5)</span>: +12, +10, +8 points</li>
                        <li><span class="font-semibold">Small wins (diff 6–9)</span>: +6, +4, +2, +1 points</li>
                        <li><span class="font-semibold">Very far off (diff 11+)</span>: small negative points, down to -25 for a wild miss</li>
                    </ul>
                    <p class="text-xs text-auto-muted mt-2">
                        The exact diff → points table lives in our scoring tests and service; this summary focuses on the
                        idea rather than every individual number.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold mb-2 text-sm tracking-wide uppercase text-auto-muted">Extras &amp; edge cases</h3>
                    <ul class="list-disc list-inside text-sm text-auto-muted space-y-1">
                        <li><span class="font-semibold">Fastest lap</span>: +10 if you pick the correct driver.</li>
                        <li><span class="font-semibold">DNF wager</span>: +10 for each correctly–predicted DNF, -10 for each DNF you predicted that doesn&apos;t happen (optional).</li>
                        <li><span class="font-semibold">Perfect prediction bonus</span>: +50 if every predicted driver is in the exact correct position.</li>
                        <li><span class="font-semibold">Shortened races / half points</span>: when the FIA awards half points, we halve the race score (rounded).</li>
                        <li><span class="font-semibold">Partial predictions</span>: you can predict only some positions; we score only the positions you filled in.</li>
                        <li><span class="font-semibold">Drivers that don&apos;t race (DNS/DSQ/etc.)</span>: excluded from the processed results; their prediction counts as 0 for that race.</li>
                    </ul>
                </div>
            </div>
        </x-mary-card>

        <x-mary-card class="mb-8">
            <h2 class="text-heading-2 mb-3">Sprint predictions</h2>
            <p class="text-auto-muted mb-4">
                Sprint races are scored with a lighter version of the main race rules, focused only on the top 8.
            </p>
            <ul class="list-disc list-inside text-sm text-auto-muted space-y-1 mb-3">
                <li><span class="font-semibold">Top 8 position table</span>: 0 diff → 8 points, then 7, 6, 5, 4, 3, 2, 1 for the remaining top-8 spots.</li>
                <li><span class="font-semibold">Positions 9+</span>: 0 points (no negatives).</li>
                <li><span class="font-semibold">Fastest lap</span>: +5 if you pick the correct driver.</li>
                <li><span class="font-semibold">Perfect top-8</span>: +15 bonus if you nail all of the top 8 positions in order.</li>
            </ul>
            <p class="text-auto-muted text-sm">
                Sprint scores are added to your season total just like full race scores.
            </p>
        </x-mary-card>

        <x-mary-card class="mb-8">
            <h2 class="text-heading-2 mb-3">Season predictions (preseason &amp; midseason)</h2>
            <p class="text-auto-muted mb-4">
                For preseason and midseason predictions you pick the final championship order for both drivers and teams.
                At the end of the season we compare your picks against the real standings.
            </p>
            <ul class="list-disc list-inside text-sm text-auto-muted space-y-1 mb-3">
                <li><span class="font-semibold">Same diff table</span>: we use the same position-difference table as race scoring (big rewards for being spot on, small penalties if you&apos;re way off).</li>
                <li><span class="font-semibold">Drivers &amp; constructors</span>: both driver championship order and constructor order are scored.</li>
                <li><span class="font-semibold">Perfect season bonus</span>: +50 if every predicted driver and team ends up in the exact position you picked.</li>
            </ul>
            <p class="text-auto-muted text-sm">
                These long-term predictions are scored once per season and can make a big difference to your final total.
            </p>
        </x-mary-card>

        <x-mary-card>
            <h2 class="text-heading-2 mb-3">Cancelled races &amp; admin overrides</h2>
            <ul class="list-disc list-inside text-sm text-auto-muted space-y-1 mb-3">
                <li><span class="font-semibold">Cancelled races</span>: if a race is cancelled, affected predictions are marked as cancelled and scored as 0 (no gain, no loss).</li>
                <li><span class="font-semibold">Result anomalies</span>: if the FIA updates results later (e.g. penalties), admins can rescore races or apply overrides to keep things fair.</li>
            </ul>
            <p class="text-auto-muted text-sm">
                Behind the scenes, all scoring is handled by a dedicated service and a full test suite, so the rules above are
                applied consistently for every user.
            </p>
        </x-mary-card>
    </div>
</x-layouts.layout>

