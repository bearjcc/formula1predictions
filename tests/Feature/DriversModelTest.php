<?php

use App\Models\Drivers;
use App\Models\Standings;
use App\Models\Teams;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

#region Drivers::forSeason

test('forSeason returns drivers from standings entity_ids for that season', function () {
    $team = Teams::factory()->create();
    $driver = Drivers::factory()->create([
        'driver_id' => 'ham',
        'team_id' => $team->id,
    ]);
    Standings::factory()->create([
        'season' => 2025,
        'type' => 'drivers',
        'round' => null,
        'entity_id' => 'ham',
        'entity_name' => $driver->surname,
        'position' => 1,
    ]);

    $result = Drivers::forSeason(2025, null);

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($driver->id)
        ->and($result->first()->driver_id)->toBe('ham');
});

test('forSeason deduplicates when same driver is matched by driver_id and by id', function () {
    $team = Teams::factory()->create();
    $driver = Drivers::factory()->create([
        'driver_id' => 'ham',
        'team_id' => $team->id,
    ]);
    // Standings can have entity_id as driver_id string or as numeric id; same driver may appear in both
    Standings::factory()->create([
        'season' => 2025,
        'type' => 'drivers',
        'round' => null,
        'entity_id' => 'ham',
        'entity_name' => $driver->surname,
        'position' => 1,
    ]);
    Standings::factory()->create([
        'season' => 2025,
        'type' => 'drivers',
        'round' => null,
        'entity_id' => (string) $driver->id,
        'entity_name' => $driver->surname,
        'position' => 2,
    ]);

    $result = Drivers::forSeason(2025, null);

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($driver->id)
        ->and($result->first()->driver_id)->toBe('ham');
});

test('forSeason returns empty collection when no standings for season', function () {
    $result = Drivers::forSeason(2030, null);
    expect($result)->toHaveCount(0);
});

#endregion
