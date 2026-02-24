<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// region Migration cleanup (F1-067)

test('empty foreign key relationships migration has been removed', function () {
    $migrationPath = base_path('database/migrations/2025_08_26_100104_add_foreign_key_relationships.php');

    expect(file_exists($migrationPath))->toBeFalse();
});

// endregion

