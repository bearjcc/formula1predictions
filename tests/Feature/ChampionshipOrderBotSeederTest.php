<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\User;
use Database\Seeders\ChampionshipOrderBotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('slow');

test('championship order bot seeder creates SeasonBot user', function () {
    Races::factory()->create(['season' => 2023, 'round' => 1]);
    Standings::factory()->create([
        'season' => 2022,
        'type' => 'drivers',
        'round' => null,
        'entity_id' => 'driver1',
        'entity_name' => 'Driver One',
        'position' => 1,
    ]);

    (new ChampionshipOrderBotSeeder)->run();

    $bot = User::where('email', 'seasonbot@example.com')->first();
    expect($bot)->not->toBeNull();
    expect($bot->name)->toBe('SeasonBot');
});

test('championship order bot uses current season standings before each round', function () {
    $d1 = Drivers::factory()->create(['driver_id' => 'd1']);
    $d2 = Drivers::factory()->create(['driver_id' => 'd2']);
    Races::factory()->create(['season' => 2023, 'round' => 1]);
    Races::factory()->create(['season' => 2023, 'round' => 2]);
    // Standings after round 1: d2 first, d1 second
    Standings::factory()->create([
        'season' => 2023, 'type' => 'drivers', 'round' => 1,
        'entity_id' => 'd2', 'entity_name' => 'D2', 'position' => 1,
    ]);
    Standings::factory()->create([
        'season' => 2023, 'type' => 'drivers', 'round' => 1,
        'entity_id' => 'd1', 'entity_name' => 'D1', 'position' => 2,
    ]);

    (new ChampionshipOrderBotSeeder)->run();

    $predRound2 = Prediction::where('user_id', User::where('email', 'seasonbot@example.com')->first()->id)
        ->where('season', 2023)->where('race_round', 2)->first();
    expect($predRound2)->not->toBeNull();
    expect($predRound2->prediction_data['driver_order'])->toEqual([$d2->id, $d1->id]);
});

test('championship order bot run is idempotent', function () {
    Races::factory()->create(['season' => 2024, 'round' => 1]);
    Standings::factory()->create([
        'season' => 2023, 'type' => 'drivers', 'round' => null,
        'entity_id' => 'x', 'entity_name' => 'X', 'position' => 1,
    ]);

    (new ChampionshipOrderBotSeeder)->run();
    (new ChampionshipOrderBotSeeder)->run();

    expect(User::where('email', 'seasonbot@example.com')->count())->toBe(1);
    expect(Prediction::where('season', 2024)->where('race_round', 1)->count())->toBe(1);
});
