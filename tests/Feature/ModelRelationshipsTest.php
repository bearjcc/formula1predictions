<?php

use App\Models\{Circuits, Countries, Drivers, Prediction, Races, Standings, Teams, User};

test('user can have many predictions', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create();
    $predictions = Prediction::factory()->count(3)->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race'
    ]);

    expect($user->predictions)->toHaveCount(3);
    expect($user->predictions->first())->toBeInstanceOf(Prediction::class);
});

test('prediction belongs to user', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race'
    ]);

    expect($prediction->user)->toBeInstanceOf(User::class);
    expect($prediction->user->id)->toBe($user->id);
});

test('team can have many drivers', function () {
    $team = Teams::factory()->create();
    $drivers = Drivers::factory()->count(2)->create(['team_id' => $team->id]);

    expect($team->drivers)->toHaveCount(2);
    expect($team->drivers->first())->toBeInstanceOf(Drivers::class);
});

test('driver belongs to team', function () {
    $team = Teams::factory()->create();
    $driver = Drivers::factory()->create(['team_id' => $team->id]);

    expect($driver->team)->toBeInstanceOf(Teams::class);
    expect($driver->team->id)->toBe($team->id);
});

test('circuit can have many races', function () {
    $circuit = Circuits::factory()->create();
    $races = Races::factory()->count(3)->create(['circuit_id' => $circuit->id]);

    expect($circuit->races)->toHaveCount(3);
    expect($circuit->races->first())->toBeInstanceOf(Races::class);
});

test('race belongs to circuit', function () {
    $circuit = Circuits::factory()->create();
    $race = Races::factory()->create(['circuit_id' => $circuit->id]);

    expect($race->circuit)->toBeInstanceOf(Circuits::class);
    expect($race->circuit->id)->toBe($circuit->id);
});

test('race can have many predictions', function () {
    $race = Races::factory()->create();
    $predictions = Prediction::factory()->count(2)->create([
        'race_id' => $race->id,
        'type' => 'race'
    ]);

    expect($race->predictions)->toHaveCount(2);
    expect($race->predictions->first())->toBeInstanceOf(Prediction::class);
});

test('prediction belongs to race', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'race_id' => $race->id,
        'type' => 'race'
    ]);

    expect($prediction->race)->toBeInstanceOf(Races::class);
    expect($prediction->race->id)->toBe($race->id);
});

test('driver can have many standings', function () {
    $driver = Drivers::factory()->create();
    $standings = Standings::factory()->count(3)->create([
        'entity_id' => $driver->driver_id,
        'type' => 'drivers'
    ]);

    expect($driver->standings)->toHaveCount(3);
    expect($driver->standings->first())->toBeInstanceOf(Standings::class);
});

test('team can have many standings', function () {
    $team = Teams::factory()->create();
    $standings = Standings::factory()->count(3)->create([
        'entity_id' => $team->team_id,
        'type' => 'constructors'
    ]);

    expect($team->standings)->toHaveCount(3);
    expect($team->standings->first())->toBeInstanceOf(Standings::class);
});

test('race can have many standings', function () {
    $race = Races::factory()->create();
    $standings = Standings::factory()->count(2)->create([
        'season' => $race->season,
        'round' => $race->round
    ]);

    expect($race->standings)->toHaveCount(2);
    expect($race->standings->first())->toBeInstanceOf(Standings::class);
});

test('foreign key constraints work correctly', function () {
    // Test that deleting a user cascades to predictions
    $user = User::factory()->create();
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race'
    ]);
    
    $user->delete();
    
    expect(Prediction::find($prediction->id))->toBeNull();
});

test('model relationships return correct data types', function () {
    $user = User::factory()->create();
    $team = Teams::factory()->create();
    $driver = Drivers::factory()->create(['team_id' => $team->id]);
    $circuit = Circuits::factory()->create();
    $race = Races::factory()->create(['circuit_id' => $circuit->id]);
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race'
    ]);

    expect($prediction->user)->toBeInstanceOf(User::class);
    expect($prediction->race)->toBeInstanceOf(Races::class);
    expect($driver->team)->toBeInstanceOf(Teams::class);
    expect($race->circuit)->toBeInstanceOf(Circuits::class);
});
