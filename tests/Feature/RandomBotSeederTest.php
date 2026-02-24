<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\User;
use Database\Seeders\RandomBotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('slow');

test('random bot seeder creates user', function () {
    $d = Drivers::factory()->create();
    Races::factory()->create(['season' => 2023, 'round' => 1]);
    Standings::factory()->create([
        'season' => 2023, 'type' => 'drivers', 'round' => null,
        'entity_id' => $d->driver_id, 'entity_name' => $d->name, 'position' => 1,
    ]);

    (new RandomBotSeeder)->run();

    $bot = User::where('email', 'randombot@example.com')->first();
    expect($bot)->not->toBeNull();
    expect($bot->name)->toBe('RandomBot');
});

test('random bot creates one prediction per race with shuffled order', function () {
    $d1 = Drivers::factory()->create(['driver_id' => 'd1']);
    $d2 = Drivers::factory()->create(['driver_id' => 'd2']);
    Races::factory()->create(['season' => 2023, 'round' => 1]);
    Standings::factory()->create([
        'season' => 2023, 'type' => 'drivers', 'round' => null,
        'entity_id' => 'd1', 'entity_name' => 'D1', 'position' => 1,
    ]);
    Standings::factory()->create([
        'season' => 2023, 'type' => 'drivers', 'round' => null,
        'entity_id' => 'd2', 'entity_name' => 'D2', 'position' => 2,
    ]);

    (new RandomBotSeeder)->run();

    $bot = User::where('email', 'randombot@example.com')->first();
    $pred = Prediction::where('user_id', $bot->id)->where('season', 2023)->where('race_round', 1)->first();
    expect($pred)->not->toBeNull();
    expect($pred->prediction_data['driver_order'])->toHaveCount(2);
    expect($pred->prediction_data['driver_order'])->toContain($d1->id, $d2->id);
});

test('random bot run is idempotent', function () {
    $d = Drivers::factory()->create();
    Races::factory()->create(['season' => 2024, 'round' => 1]);
    Standings::factory()->create([
        'season' => 2024, 'type' => 'drivers', 'round' => null,
        'entity_id' => $d->driver_id, 'entity_name' => $d->name, 'position' => 1,
    ]);

    (new RandomBotSeeder)->run();
    (new RandomBotSeeder)->run();

    $bot = User::where('email', 'randombot@example.com')->first();
    expect($bot)->not->toBeNull();
    $botPredictions = Prediction::where('user_id', $bot->id)
        ->where('season', 2024)
        ->where('race_round', 1)
        ->get();
    expect($botPredictions)->toHaveCount(1);
});
