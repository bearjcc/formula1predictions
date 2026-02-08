<?php

use App\Http\Requests\StorePredictionRequest;
use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

test('database migrations work correctly', function () {
    // Test that we can create basic models
    $user = User::factory()->create();
    expect($user)->toBeInstanceOf(User::class);

    $driver = Drivers::factory()->create();
    expect($driver)->toBeInstanceOf(Drivers::class);

    $team = Teams::factory()->create();
    expect($team)->toBeInstanceOf(Teams::class);
});

test('form validation works correctly', function () {
    // Create drivers first
    $drivers = collect();
    for ($i = 1; $i <= 20; $i++) {
        $drivers->push(Drivers::factory()->create(['driver_id' => $i]));
    }

    $request = new StorePredictionRequest;

    // Test valid data
    $validData = [
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'driver_order' => $drivers->pluck('id')->toArray(),
            'fastest_lap' => $drivers->first()->id,
        ],
        'notes' => 'Test prediction',
    ];

    $validator = Validator::make($validData, $request->rules(), $request->messages());
    expect($validator->passes())->toBeTrue();

    // Test invalid data
    $invalidData = [
        'type' => 'invalid_type',
        'season' => 1800,
    ];

    $validator = Validator::make($invalidData, $request->rules(), $request->messages());
    expect($validator->fails())->toBeTrue();
});

test('prediction model can be created', function () {
    $user = User::factory()->create();
    $driver = Drivers::factory()->create();

    $prediction = Prediction::create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'driver_order' => [$driver->id],
            'fastest_lap' => $driver->id,
        ],
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    expect($prediction)->toBeInstanceOf(Prediction::class);
    expect($prediction->user_id)->toBe($user->id);
    expect($prediction->type)->toBe('race');
});

test('model relationships work', function () {
    $user = User::factory()->create();
    $driver = Drivers::factory()->create();
    $team = Teams::factory()->create();

    // Test user can have predictions
    $prediction = Prediction::create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => ['driver_order' => [$driver->id]],
        'status' => 'submitted',
    ]);

    expect($user->predictions)->toHaveCount(1);
    expect($prediction->user)->toBeInstanceOf(User::class);
});

test('form request authorization works', function () {
    $request = new StorePredictionRequest;

    // Should require authentication
    expect($request->authorize())->toBeFalse();

    // Mock authenticated user
    $user = User::factory()->create();
    Auth::login($user);

    expect($request->authorize())->toBeTrue();
});
