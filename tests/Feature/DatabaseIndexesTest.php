<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// region Predictions indexes (F1-068)

test('predictions table has an index on race_id', function () {
    $indexes = DB::select("PRAGMA index_list('predictions')");

    $hasRaceIdIndex = false;

    foreach ($indexes as $index) {
        // For SQLite, the index name is in the "name" property.
        $indexName = $index->name ?? null;
        if (! $indexName) {
            continue;
        }

        $columns = DB::select("PRAGMA index_info('{$indexName}')");
        foreach ($columns as $column) {
            if (($column->name ?? null) === 'race_id') {
                $hasRaceIdIndex = true;
                break 2;
            }
        }
    }

    expect($hasRaceIdIndex)->toBeTrue();
});

// endregion

