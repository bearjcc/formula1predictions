<?php

use App\Models\User;

it('can run parallel browser tests for dashboard', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
})->parallel();

it('can run parallel browser tests for races page', function () {
    $user = User::factory()->create();

    $page = visit('/races')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('Races')
        ->assertNoJavascriptErrors();
})->parallel();

it('can run parallel browser tests for predictions page', function () {
    $user = User::factory()->create();

    $page = visit('/predictions')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('Predictions')
        ->assertNoJavascriptErrors();
})->parallel();

it('can run parallel browser tests for standings page', function () {
    $user = User::factory()->create();

    $page = visit('/standings')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('Standings')
        ->assertNoJavascriptErrors();
})->parallel();
