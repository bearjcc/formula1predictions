<?php

use App\Models\User;

it('dashboard screenshots match baseline', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertScreenshotsMatches();
});

it('races page screenshots match baseline', function () {
    $user = User::factory()->create();

    $page = visit('/races')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertScreenshotsMatches();
});

it('predictions page screenshots match baseline', function () {
    $user = User::factory()->create();

    $page = visit('/predictions')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertScreenshotsMatches();
});

it('dark mode screenshots match baseline', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->desktop()
        ->inDarkMode();

    $page->assertScreenshotsMatches();
});

it('mobile screenshots match baseline', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->mobile()
        ->inLightMode();

    $page->assertScreenshotsMatches();
});
