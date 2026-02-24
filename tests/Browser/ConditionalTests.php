<?php

uses()->group('browser', 'slow');

use App\Models\User;

it('runs on CI but not locally', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->desktop();

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
})->skipLocally();

it('runs locally but not on CI', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->mobile();

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
})->skipOnCi();

it('tests multiple browsers', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->desktop();

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
})->browsers(['chromium', 'firefox', 'webkit']);

it('tests with custom viewport', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->desktop()
        ->viewport(1920, 1080);

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
});
