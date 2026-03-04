<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\User;
use Database\Seeders\ClairvoyantBotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('slow');

test('clairvoyant bot seeder creates ClairvoyantBot user and only seeds 2025', function () {
    $d1 = Drivers::factory()->create(['driver_id' => 'c1']);
    $d2 = Drivers::factory()->create(['driver_id' => 'c2']);
    Standings::factory()->create([
        'season' => 2025, 'type' => 'drivers', 'round' => null,
        'entity_id' => 'c1', 'entity_name' => 'C1', 'position' => 1,
    ]);
    Standings::factory()->create([
        'season' => 2025, 'type' => 'drivers', 'round' => null,
        'entity_id' => 'c2', 'entity_name' => 'C2', 'position' => 2,
    ]);
    Races::factory()->create(['season' => 2025, 'round' => 1, 'has_sprint' => false]);
    Races::factory()->create(['season' => 2025, 'round' => 2, 'has_sprint' => true]);

    (new ClairvoyantBotSeeder)->run();

    $bot = User::where('email', 'clairvoyantbot@example.com')->first();
    expect($bot)->not->toBeNull();
    expect($bot->name)->toBe('ClairvoyantBot');

    $racePreds = Prediction::where('user_id', $bot->id)->where('type', 'race')->where('season', 2025)->get();
    expect($racePreds->count())->toBe(2);
    foreach ($racePreds as $p) {
        expect($p->getPredictedDriverOrder())->toEqual([$d1->driver_id, $d2->driver_id]);
    }

    $sprintPreds = Prediction::where('user_id', $bot->id)->where('type', 'sprint')->where('season', 2025)->get();
    expect($sprintPreds->count())->toBe(1);
    expect($sprintPreds->first()->race_round)->toBe(2);
    expect($sprintPreds->first()->getPredictedDriverOrder())->toEqual([$d1->driver_id, $d2->driver_id]);
});

test('clairvoyant bot skips when no 2025 final standings', function () {
    Races::factory()->create(['season' => 2025, 'round' => 1]);

    (new ClairvoyantBotSeeder)->run();

    $bot = User::where('email', 'clairvoyantbot@example.com')->first();
    expect($bot)->not->toBeNull();
    expect(Prediction::where('user_id', $bot->id)->where('season', 2025)->count())->toBe(0);
});

test('clairvoyant bot prediction data has only driver_order', function () {
    $d1 = Drivers::factory()->create(['driver_id' => 'x1']);
    Standings::factory()->create([
        'season' => 2025, 'type' => 'drivers', 'round' => null,
        'entity_id' => 'x1', 'entity_name' => 'X1', 'position' => 1,
    ]);
    Races::factory()->create(['season' => 2025, 'round' => 1]);

    (new ClairvoyantBotSeeder)->run();

    $pred = Prediction::where('season', 2025)->where('race_round', 1)->where('type', 'race')->first();
    expect($pred)->not->toBeNull();
    expect($pred->prediction_data)->toHaveKey('driver_order');
    expect(array_keys($pred->prediction_data))->toEqual(['driver_order']);
});
