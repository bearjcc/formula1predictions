<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Services\F1ApiService;
use Database\Seeders\LastYearChampionshipOrderBotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('slow');

test('last year order bot seeder creates Last Year Order Bot user', function () {
    $d1 = Drivers::factory()->create(['driver_id' => 'driver_a']);
    Races::factory()->create(['season' => 2025, 'round' => 1]);

    $mockF1 = Mockery::mock(F1ApiService::class);
    $mockF1->shouldReceive('fetchDriversChampionship')->with(2024)->andReturn([
        'drivers_championship' => [['driverId' => 'driver_a']],
    ]);
    $mockF1->shouldReceive('syncDriversForSeason')->with(2024);
    app()->instance(F1ApiService::class, $mockF1);

    config(['f1.bot_seed_seasons' => [2025]]);
    (new LastYearChampionshipOrderBotSeeder)->run();

    $bot = User::where('email', 'lastyearorderbot@example.com')->first();
    expect($bot)->not->toBeNull();
    expect($bot->name)->toBe('Last Year Order Bot');
});

test('last year order bot seeder seeds multiple seasons when config is set', function () {
    $d1 = Drivers::factory()->create(['driver_id' => 'driver_a']);
    $d2 = Drivers::factory()->create(['driver_id' => 'driver_b']);
    Races::factory()->create(['season' => 2025, 'round' => 1]);
    Races::factory()->create(['season' => 2025, 'round' => 2]);
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

    config(['f1.bot_seed_seasons' => [2025, 2026]]);
    (new LastYearChampionshipOrderBotSeeder)->run();

    $bot = User::where('email', 'lastyearorderbot@example.com')->first();
    expect($bot)->not->toBeNull();

    expect(Prediction::where('user_id', $bot->id)->where('season', 2025)->count())->toBe(2);
    expect(Prediction::where('user_id', $bot->id)->where('season', 2026)->count())->toBe(1);

    $pred2025r1 = Prediction::where('user_id', $bot->id)->where('season', 2025)->where('race_round', 1)->first();
    $pred2026r1 = Prediction::where('user_id', $bot->id)->where('season', 2026)->where('race_round', 1)->first();
    expect($pred2025r1->prediction_data['driver_order'])->toEqual([$d1->id, $d2->id]);
    expect($pred2026r1->prediction_data['driver_order'])->toEqual([$d1->id, $d2->id]);
});

test('last year order bot seeder uses current season when bot_seed_seasons not set', function () {
    $d1 = Drivers::factory()->create(['driver_id' => 'driver_x']);
    Races::factory()->create(['season' => 2030, 'round' => 1]);

    $mockF1 = Mockery::mock(F1ApiService::class);
    $mockF1->shouldReceive('fetchDriversChampionship')->with(2029)->andReturn([
        'drivers_championship' => [['driverId' => 'driver_x']],
    ]);
    $mockF1->shouldReceive('syncDriversForSeason')->with(2029);
    app()->instance(F1ApiService::class, $mockF1);

    config(['f1.current_season' => 2030]);
    config(['f1.bot_seed_seasons' => null]);
    (new LastYearChampionshipOrderBotSeeder)->run();

    $bot = User::where('email', 'lastyearorderbot@example.com')->first();
    expect(Prediction::where('user_id', $bot->id)->where('season', 2030)->count())->toBe(1);
});

test('last year order bot seeder skips season when no races', function () {
    $d1 = Drivers::factory()->create(['driver_id' => 'driver_a']);
    Races::factory()->create(['season' => 2025, 'round' => 1]);
    // No races for 2026

    $mockF1 = Mockery::mock(F1ApiService::class);
    $mockF1->shouldReceive('fetchDriversChampionship')->with(2024)->andReturn([
        'drivers_championship' => [['driverId' => 'driver_a']],
    ]);
    $mockF1->shouldReceive('fetchDriversChampionship')->with(2025)->andReturn([
        'drivers_championship' => [['driverId' => 'driver_a']],
    ]);
    $mockF1->shouldReceive('syncDriversForSeason')->with(2024);
    $mockF1->shouldReceive('syncDriversForSeason')->with(2025);
    app()->instance(F1ApiService::class, $mockF1);

    config(['f1.bot_seed_seasons' => [2025, 2026]]);
    (new LastYearChampionshipOrderBotSeeder)->run();

    $bot = User::where('email', 'lastyearorderbot@example.com')->first();
    expect(Prediction::where('user_id', $bot->id)->where('season', 2025)->count())->toBe(1);
    expect(Prediction::where('user_id', $bot->id)->where('season', 2026)->count())->toBe(0);
});

test('last year order bot run is idempotent', function () {
    $d1 = Drivers::factory()->create(['driver_id' => 'driver_a']);
    Races::factory()->create(['season' => 2025, 'round' => 1]);

    $mockF1 = Mockery::mock(F1ApiService::class);
    $mockF1->shouldReceive('fetchDriversChampionship')->with(2024)->andReturn([
        'drivers_championship' => [['driverId' => 'driver_a']],
    ]);
    $mockF1->shouldReceive('syncDriversForSeason')->with(2024);
    app()->instance(F1ApiService::class, $mockF1);

    config(['f1.bot_seed_seasons' => [2025]]);
    (new LastYearChampionshipOrderBotSeeder)->run();
    (new LastYearChampionshipOrderBotSeeder)->run();

    expect(User::where('email', 'lastyearorderbot@example.com')->count())->toBe(1);
    expect(Prediction::where('season', 2025)->where('race_round', 1)->count())->toBe(1);
});
