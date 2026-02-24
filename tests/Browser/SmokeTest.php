<?php

uses()->group('browser', 'slow');

use App\Models\User;
use Illuminate\Support\Facades\Config;

it('can visit all main pages without smoke', function () {
    $user = User::factory()->create();
    $year = Config::get('f1.current_season', 2026);

    $routes = [
        '/',
        '/dashboard',
        '/predictions',
        "/{$year}/races",
        "/{$year}/standings",
        "/{$year}/standings/drivers",
        "/{$year}/standings/teams",
        '/leaderboard',
        '/analytics',
        '/settings/profile',
    ];

    $pages = visit($routes)
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $pages->assertNoSmoke();
});

it('can visit all main pages in dark mode without smoke', function () {
    $user = User::factory()->create();
    $year = Config::get('f1.current_season', 2026);

    $routes = [
        '/',
        '/dashboard',
        '/predictions',
        "/{$year}/races",
        "/{$year}/standings",
        "/{$year}/standings/drivers",
        "/{$year}/standings/teams",
        '/leaderboard',
        '/analytics',
        '/settings/profile',
    ];

    $pages = visit($routes)
        ->actingAs($user)
        ->on()->desktop()
        ->inDarkMode();

    $pages->assertNoSmoke();
});

it('can visit all main pages on mobile without smoke', function () {
    $user = User::factory()->create();
    $year = Config::get('f1.current_season', 2026);

    $routes = [
        '/',
        '/dashboard',
        '/predictions',
        "/{$year}/races",
        "/{$year}/standings",
        "/{$year}/standings/drivers",
        "/{$year}/standings/teams",
        '/countries',
        '/leaderboard',
        '/analytics',
        '/settings/profile',
    ];

    $pages = visit($routes)
        ->actingAs($user)
        ->on()->mobile()
        ->inLightMode();

    $pages->assertNoSmoke();
});

it('can visit all main pages on tablet without smoke', function () {
    $user = User::factory()->create();
    $year = Config::get('f1.current_season', 2026);

    $routes = [
        '/',
        '/dashboard',
        '/predictions',
        "/{$year}/races",
        "/{$year}/standings",
        "/{$year}/standings/drivers",
        "/{$year}/standings/teams",
        '/countries',
        '/leaderboard',
        '/analytics',
        '/settings/profile',
    ];

    $pages = visit($routes)
        ->actingAs($user)
        ->on()->tablet()
        ->inLightMode();

    $pages->assertNoSmoke();
});
