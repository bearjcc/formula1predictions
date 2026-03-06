<?php

declare(strict_types=1);

use App\Livewire\Predictions\PredictionForm;
use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Teams;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('preseason prediction form saves successfully', function () {
    Carbon::setTestNow('2026-03-01 12:00:00');

    try {
        $user = User::factory()->create();
        $teams = Teams::factory()->count(3)->create();

        foreach ($teams as $team) {
            Drivers::factory()->count(2)->create(['team_id' => $team->id]);
        }

        Races::factory()->create([
            'season' => 2026,
            'round' => 1,
            'status' => 'upcoming',
            'date' => Carbon::parse('2026-03-15'),
            'qualifying_start' => Carbon::parse('2026-03-14 12:00:00'),
        ]);

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->set('type', 'preseason')
            ->set('season', 2026)
            ->set('teamOrder', $teams->pluck('id')->all())
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('predictions.index'));

        $prediction = Prediction::where('user_id', $user->id)->first();

        expect($prediction)->not->toBeNull()
            ->and($prediction->type)->toBe('preseason')
            ->and($prediction->status)->toBe('submitted')
            ->and($prediction->race_round)->toBeNull()
            ->and($prediction->getConstructorOrder())->toHaveCount(3);
    } finally {
        Carbon::setTestNow();
    }
});

test('midseason prediction form saves successfully', function () {
    $user = User::factory()->create();
    $teams = Teams::factory()->count(3)->create();
    $drivers = collect();

    foreach ($teams as $team) {
        $drivers = $drivers->merge(Drivers::factory()->count(2)->create(['team_id' => $team->id]));
    }

    Livewire::actingAs($user)
        ->test(PredictionForm::class)
        ->set('type', 'midseason')
        ->set('season', 2026)
        ->set('teamOrder', $teams->pluck('id')->all())
        ->set('driverChampionship', $drivers->pluck('id')->all())
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('predictions.index'));

    $prediction = Prediction::where('user_id', $user->id)->first();

    expect($prediction)->not->toBeNull()
        ->and($prediction->type)->toBe('midseason')
        ->and($prediction->status)->toBe('submitted')
        ->and($prediction->getConstructorOrder())->toHaveCount(3)
        ->and($prediction->getDriverChampionshipOrder())->toHaveCount(6);
});
