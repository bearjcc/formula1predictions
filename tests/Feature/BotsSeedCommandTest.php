<?php

use App\Models\Drivers;
use App\Models\Races;
use App\Models\Standings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('bots:seed runs all bot seeders', function () {
    Races::factory()->create(['season' => 2023, 'round' => 1]);
    Standings::factory()->create([
        'season' => 2022, 'type' => 'drivers', 'round' => null,
        'entity_id' => 'x', 'entity_name' => 'X', 'position' => 1,
    ]);
    Drivers::factory()->create(['driver_id' => 'x']);

    $this->artisan('bots:seed')
        ->assertSuccessful();

    expect(User::where('email', 'lastbot@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'seasonbot@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'randombot@example.com')->exists())->toBeTrue();
});

test('bots:seed --only runs specified bots only', function () {
    Races::factory()->create(['season' => 2023, 'round' => 1]);
    Standings::factory()->create([
        'season' => 2022, 'type' => 'drivers', 'round' => null,
        'entity_id' => 'x', 'entity_name' => 'X', 'position' => 1,
    ]);

    $this->artisan('bots:seed', ['--only' => 'season,random'])
        ->assertSuccessful();

    expect(User::where('email', 'seasonbot@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'randombot@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'championshipbot@example.com')->exists())->toBeFalse();
});

test('bots:seed --only fails on unknown bot name', function () {
    $this->artisan('bots:seed', ['--only' => 'championship-order,invalid-bot'])
        ->assertFailed();
});
