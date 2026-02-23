<?php

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('allowsPredictions returns false when past qualifying deadline', function () {
    $qualifyingStart = Carbon::now()->addHours(2); // qualifying in 2h, deadline in 1h
    $deadline = $qualifyingStart->copy()->subHour();

    $race = Races::factory()->create([
        'date' => Carbon::now()->addDays(2),
        'status' => 'upcoming',
        'qualifying_start' => $qualifyingStart,
    ]);

    expect($race->allowsPredictions())->toBeTrue();

    Carbon::setTestNow($deadline->copy()->subMinute());
    expect($race->fresh()->allowsPredictions())->toBeTrue();

    Carbon::setTestNow($deadline->copy()->addMinute());
    expect($race->fresh()->allowsPredictions())->toBeFalse();

    Carbon::setTestNow();
});

test('allowsPredictions returns true when qualifying_start is null (legacy)', function () {
    $race = Races::factory()->create([
        'date' => Carbon::now()->addDays(2),
        'status' => 'upcoming',
        'qualifying_start' => null,
    ]);

    expect($race->allowsPredictions())->toBeTrue();
});

test('allowsSprintPredictions returns false when past sprint qualifying deadline', function () {
    $sprintQualyStart = Carbon::now()->addMinutes(30);
    $deadline = $sprintQualyStart->copy()->subHour();

    $race = Races::factory()->create([
        'date' => Carbon::now()->addDays(2),
        'status' => 'upcoming',
        'has_sprint' => true,
        'qualifying_start' => Carbon::now()->addDays(1),
        'sprint_qualifying_start' => $sprintQualyStart,
    ]);

    Carbon::setTestNow($deadline->copy()->subMinute());
    expect($race->fresh()->allowsSprintPredictions())->toBeTrue();

    Carbon::setTestNow($deadline->copy()->addMinute());
    expect($race->fresh()->allowsSprintPredictions())->toBeFalse();

    Carbon::setTestNow();
});

test('lock predictions past deadline command locks submitted predictions', function () {
    $qualifyingStart = Carbon::now()->addMinutes(20); // deadline (Q-1h) already passed
    $race = Races::factory()->create([
        'date' => Carbon::now()->addDays(1),
        'status' => 'upcoming',
        'qualifying_start' => $qualifyingStart,
    ]);

    $prediction = Prediction::factory()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    expect($prediction->status)->toBe('submitted');

    $this->artisan('predictions:lock-past-deadline')
        ->assertSuccessful();

    $prediction->refresh();
    expect($prediction->status)->toBe('locked');
    expect($prediction->locked_at)->not->toBeNull();
});

test('lock predictions past deadline does not lock when before deadline', function () {
    $qualifyingStart = Carbon::now()->addHours(2); // deadline in 1 hour
    $race = Races::factory()->create([
        'date' => Carbon::now()->addDays(1),
        'status' => 'upcoming',
        'qualifying_start' => $qualifyingStart,
    ]);

    $prediction = Prediction::factory()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'status' => 'submitted',
    ]);

    $this->artisan('predictions:lock-past-deadline')
        ->assertSuccessful();

    $prediction->refresh();
    expect($prediction->status)->toBe('submitted');
});

test('lock predictions past deadline does not lock when qualifying_start is null', function () {
    $race = Races::factory()->create([
        'date' => Carbon::now()->addDays(1),
        'status' => 'upcoming',
        'qualifying_start' => null,
    ]);

    $prediction = Prediction::factory()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'status' => 'submitted',
    ]);

    $this->artisan('predictions:lock-past-deadline')
        ->assertSuccessful();

    $prediction->refresh();
    expect($prediction->status)->toBe('submitted');
});

test('getRacePredictionDeadline returns null when qualifying_start is null', function () {
    $race = Races::factory()->create([
        'qualifying_start' => null,
    ]);

    expect($race->getRacePredictionDeadline())->toBeNull();
});

test('getSprintPredictionDeadline returns null when sprint_qualifying_start is null', function () {
    $race = Races::factory()->create([
        'has_sprint' => true,
        'sprint_qualifying_start' => null,
    ]);

    expect($race->getSprintPredictionDeadline())->toBeNull();
});

test('lock predictions past deadline locks preseason when first race deadline passed', function () {
    $qualifyingStart = Carbon::now()->addMinutes(20);
    Races::factory()->create([
        'season' => 2025,
        'round' => 1,
        'date' => Carbon::now()->addDays(1),
        'status' => 'upcoming',
        'qualifying_start' => $qualifyingStart,
    ]);

    $preseason = Prediction::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'preseason',
        'season' => 2025,
        'race_id' => null,
        'race_round' => null,
        'status' => 'submitted',
    ]);

    $this->artisan('predictions:lock-past-deadline')
        ->assertSuccessful();

    $preseason->refresh();
    expect($preseason->status)->toBe('locked');
});

test('preseason prediction is not editable when first race deadline passed', function () {
    $deadline = Carbon::now()->subHour();
    $qualifyingStart = $deadline->copy()->addHour();
    Races::factory()->create([
        'season' => 2026,
        'round' => 1,
        'date' => Carbon::now()->addDay(),
        'status' => 'upcoming',
        'qualifying_start' => $qualifyingStart,
    ]);

    $preseason = Prediction::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'preseason',
        'season' => 2026,
        'status' => 'submitted',
    ]);

    Carbon::setTestNow($deadline->copy()->addMinute());
    expect($preseason->fresh()->isEditable())->toBeFalse();
    Carbon::setTestNow();
});

test('preseason prediction is editable when before first race deadline', function () {
    $deadline = Carbon::now()->addHours(2);
    $qualifyingStart = $deadline->copy()->addHour();
    Races::factory()->create([
        'season' => 2027,
        'round' => 1,
        'date' => Carbon::now()->addDays(2),
        'status' => 'upcoming',
        'qualifying_start' => $qualifyingStart,
    ]);

    $preseason = Prediction::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'preseason',
        'season' => 2027,
        'status' => 'submitted',
    ]);

    Carbon::setTestNow($deadline->copy()->subMinute());
    expect($preseason->fresh()->isEditable())->toBeTrue();
    Carbon::setTestNow();
});
