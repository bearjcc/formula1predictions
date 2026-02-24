<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\User;
use App\Services\F1ApiService;
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

test('bots:seed --only=last-year-order --season=2025,2026 creates predictions for both seasons', function () {
    Drivers::factory()->create(['driver_id' => 'driver_a']);
    Drivers::factory()->create(['driver_id' => 'driver_b']);
    Races::factory()->create(['season' => 2025, 'round' => 1]);
    Races::factory()->create(['season' => 2026, 'round' => 1]);

    $mockF1 = Mockery::mock(F1ApiService::class);
    $mockF1->shouldReceive('fetchDriversChampionship')->with(2024)->andReturn([
        'drivers_championship' => [['driverId' => 'driver_a'], ['driverId' => 'driver_b']],
    ]);
    $mockF1->shouldReceive('fetchDriversChampionship')->with(2025)->andReturn([
        'drivers_championship' => [['driverId' => 'driver_a'], ['driverId' => 'driver_b']],
    ]);
    $mockF1->shouldReceive('syncDriversForSeason')->with(2024);
    $mockF1->shouldReceive('syncDriversForSeason')->with(2025);
    app()->instance(F1ApiService::class, $mockF1);

    $this->artisan('bots:seed', ['--only' => 'last-year-order', '--season' => '2025,2026'])
        ->assertSuccessful();

    $bot = User::where('email', 'lastyearorderbot@example.com')->first();
    expect($bot)->not->toBeNull();
    expect(Prediction::where('user_id', $bot->id)->where('season', 2025)->count())->toBe(1);
    expect(Prediction::where('user_id', $bot->id)->where('season', 2026)->count())->toBe(1);
});
