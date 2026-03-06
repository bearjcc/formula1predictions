<?php

use App\Models\Teams;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * RB, Racing Bulls, and Visa Cash App RB (VCARB) are the same team; 2026 they go by "RB".
 * Merge duplicate team records into a single canonical RB team and update drivers, predictions, standings.
 */
return new class extends Migration
{
    private const RB_ALIAS_NAMES = ['RB', 'Racing Bulls', 'Visa Cash App RB', 'VCARB', 'RB F1 Team'];

    public function up(): void
    {
        $teams = Teams::whereIn('team_name', self::RB_ALIAS_NAMES)->get();

        if ($teams->isEmpty()) {
            return;
        }

        // Prefer "RB" then team with most drivers
        $canonical = $teams->sortByDesc(fn (Teams $t) => ($t->team_name === 'RB' ? 1000 : 0) + $t->drivers()->count())->first();
        $duplicateIds = $teams->where('id', '!=', $canonical->id)->pluck('id')->all();
        $duplicateIdSet = array_flip($duplicateIds);
        $canonicalId = $canonical->id;
        $canonicalTeamId = $canonical->team_id;

        if ($canonical->team_name !== 'RB') {
            $canonical->update(['team_name' => 'RB']);
        }

        // Move all drivers from duplicate teams to canonical
        DB::table('drivers')
            ->whereIn('team_id', $duplicateIds)
            ->update(['team_id' => $canonicalId]);

        // Predictions: prediction_data.team_order and .teammate_battles use numeric team ids
        $predictions = DB::table('predictions')->get();
        foreach ($predictions as $row) {
            $data = json_decode($row->prediction_data, true);
            if (! is_array($data)) {
                continue;
            }
            $changed = false;

            if (isset($data['team_order']) && is_array($data['team_order'])) {
                $seen = [];
                $newOrder = [];
                foreach ($data['team_order'] as $tid) {
                    $id = (int) $tid;
                    $resolved = isset($duplicateIdSet[$id]) ? $canonicalId : $id;
                    if (! isset($seen[$resolved])) {
                        $newOrder[] = $resolved;
                        $seen[$resolved] = true;
                    }
                }
                if ($newOrder !== $data['team_order']) {
                    $data['team_order'] = $newOrder;
                    $changed = true;
                }
            }

            if (isset($data['teammate_battles']) && is_array($data['teammate_battles'])) {
                $newBattles = [];
                foreach ($data['teammate_battles'] as $teamId => $driverId) {
                    $tid = (int) $teamId;
                    $resolved = isset($duplicateIdSet[$tid]) ? $canonicalId : $tid;
                    $newBattles[(string) $resolved] = $driverId;
                }
                if ($newBattles !== $data['teammate_battles']) {
                    $data['teammate_battles'] = $newBattles;
                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('predictions')
                    ->where('id', $row->id)
                    ->update(['prediction_data' => json_encode($data)]);
            }
        }

        // Standings: constructors use entity_id = team_id (string) per Teams relationship
        $duplicateTeamIds = $teams->whereIn('id', $duplicateIds)->pluck('team_id')->all();
        $duplicateIdsStr = array_map('strval', $duplicateIds);
        $allDuplicateEntityIds = array_merge($duplicateTeamIds, $duplicateIdsStr);
        if ($allDuplicateEntityIds !== []) {
            DB::table('standings')
                ->where('type', 'constructors')
                ->whereIn('entity_id', $allDuplicateEntityIds)
                ->update([
                    'entity_id' => $canonicalTeamId,
                    'entity_name' => 'RB',
                ]);
        }

        // Deactivate duplicate teams so they no longer appear in active lists
        Teams::whereIn('id', $duplicateIds)->update(['is_active' => false]);
    }

    public function down(): void
    {
        // Cannot reliably split merged data; no-op.
    }
};
