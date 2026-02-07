<?php

/**
 * Unit-level Form Request validation tests.
 * Tests StorePredictionRequest and UpdatePredictionRequest rules via Validator (no HTTP).
 * For HTTP integration tests, see PredictionFormValidationTest.
 */

use App\Http\Requests\StorePredictionRequest;
use App\Http\Requests\UpdatePredictionRequest;
use App\Models\Drivers;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

test('store prediction request validates correctly', function () {
    $user = User::factory()->create();
    $drivers = Drivers::factory()->count(20)->create();
    $teams = Teams::factory()->count(10)->create();

    $request = new StorePredictionRequest;
    $request->merge([
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'driver_order' => $drivers->pluck('id')->toArray(),
            'fastest_lap' => $drivers->first()->id,
        ],
        'notes' => 'Test prediction',
    ]);

    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});

test('store prediction request validates invalid type', function () {
    $request = new StorePredictionRequest;
    $request->merge([
        'type' => 'invalid_type',
        'season' => 2024,
    ]);

    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('type'))->toBeTrue();
});

test('store prediction request validates invalid season', function () {
    $request = new StorePredictionRequest;
    $request->merge([
        'type' => 'race',
        'season' => 1800, // Too early
    ]);

    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('season'))->toBeTrue();
});

test('store prediction request validates race prediction requires driver order', function () {
    $request = new StorePredictionRequest;
    $request->merge([
        'type' => 'race',
        'season' => 2024,
        'prediction_data' => [
            // Missing driver_order
        ],
    ]);

    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('prediction_data.driver_order'))->toBeTrue();
});

test('store prediction request validates preseason prediction requires team order', function () {
    $request = new StorePredictionRequest;
    $request->merge([
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            // Missing team_order
        ],
    ]);

    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('prediction_data.team_order'))->toBeTrue();
});

test('store prediction request validates driver order must have exactly 20 drivers', function () {
    $drivers = Drivers::factory()->count(25)->create();

    $request = new StorePredictionRequest;
    $request->merge([
        'type' => 'race',
        'season' => 2024,
        'prediction_data' => [
            'driver_order' => $drivers->take(15)->pluck('id')->toArray(), // Too few
        ],
    ]);

    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('prediction_data.driver_order'))->toBeTrue();
});

test('store prediction request validates team order must have exactly 10 teams', function () {
    $teams = Teams::factory()->count(15)->create();

    $request = new StorePredictionRequest;
    $request->merge([
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            'team_order' => $teams->take(8)->pluck('id')->toArray(), // Too few
        ],
    ]);

    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('prediction_data.team_order'))->toBeTrue();
});

test('store prediction request validates notes field maximum length', function () {
    $request = new StorePredictionRequest;
    $request->merge([
        'type' => 'race',
        'season' => 2024,
        'notes' => str_repeat('a', 1001), // Exceeds 1000 character limit
    ]);

    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('notes'))->toBeTrue();
});

test('update prediction request validates correctly', function () {
    $user = User::factory()->create();
    $drivers = Drivers::factory()->count(20)->create();
    $teams = Teams::factory()->count(10)->create();

    $request = new UpdatePredictionRequest;
    $request->merge([
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'driver_order' => $drivers->pluck('id')->toArray(),
            'fastest_lap' => $drivers->first()->id,
        ],
        'notes' => 'Updated prediction',
    ]);

    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});

test('form request authorization works correctly', function () {
    $request = new StorePredictionRequest;

    // Should require authentication
    expect($request->authorize())->toBeFalse();

    // Mock authenticated user
    $user = User::factory()->create();
    Auth::login($user);

    expect($request->authorize())->toBeTrue();
});
