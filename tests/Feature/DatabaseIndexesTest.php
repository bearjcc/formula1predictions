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

// region Redundant index cleanup (F1-066)

test('drivers_teams_countries_do_not_have_redundant_non_unique_indexes_on_unique_id_columns', function () {
    $tables = [
        'drivers' => 'driver_id',
        'teams' => 'team_id',
        'countries' => 'code',
    ];

    foreach ($tables as $tableName => $uniqueColumn) {
        $indexes = DB::select("PRAGMA index_list('{$tableName}')");

        $hasRedundantIndex = false;

        foreach ($indexes as $index) {
            $indexName = $index->name ?? null;

            if (! $indexName) {
                continue;
            }

            // In SQLite PRAGMA index_list, "unique" is 1 for unique indexes, 0 otherwise.
            $isUnique = (bool) ($index->unique ?? 0);

            $columns = DB::select("PRAGMA index_info('{$indexName}')");
            $columnNames = [];

            foreach ($columns as $column) {
                if (isset($column->name)) {
                    $columnNames[] = $column->name;
                }
            }

            sort($columnNames);

            if (! $isUnique && $columnNames === [$uniqueColumn]) {
                $hasRedundantIndex = true;
                break;
            }
        }

        expect($hasRedundantIndex)->toBeFalse();
    }
});

// endregion

