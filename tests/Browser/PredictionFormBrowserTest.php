<?php

uses()->group('browser', 'slow');

use App\Models\Drivers;
use App\Models\Races;
use App\Models\Teams;
use App\Models\User;

it('can create a prediction through the form', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create(['status' => 'upcoming']);
    $driver = Drivers::factory()->create();
    $team = Teams::factory()->create();

    $page = visit('/predictions/create')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('Create Prediction')
        ->click('Select Race')
        ->click($race->name)
        ->click('Select Driver')
        ->click($driver->name)
        ->click('Select Team')
        ->click($team->name)
        ->type('position', '1')
        ->click('Submit Prediction')
        ->assertSee('Prediction created successfully!')
        ->assertNoJavascriptErrors();
});

it('can edit an existing prediction', function () {
    $user = User::factory()->create();
    $prediction = \App\Models\Prediction::factory()->create(['user_id' => $user->id]);

    $page = visit("/predictions/{$prediction->id}/edit")
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('Edit Prediction')
        ->type('position', '3')
        ->click('Update Prediction')
        ->assertSee('Prediction updated successfully!')
        ->assertNoJavascriptErrors();
});

it('can delete a prediction', function () {
    $user = User::factory()->create();
    $prediction = \App\Models\Prediction::factory()->create(['user_id' => $user->id]);

    $page = visit("/predictions/{$prediction->id}")
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('Prediction Details')
        ->click('Delete Prediction')
        ->click('Confirm Delete')
        ->assertSee('Prediction deleted successfully!')
        ->assertNoJavascriptErrors();
});

it('shows validation errors for invalid prediction data', function () {
    $user = User::factory()->create();

    $page = visit('/predictions/create')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('Create Prediction')
        ->click('Submit Prediction')
        ->assertSee('The race field is required.')
        ->assertSee('The driver field is required.')
        ->assertSee('The team field is required.')
        ->assertSee('The position field is required.')
        ->assertNoJavascriptErrors();
});
