<?php

use App\Models\User;

it('can visit all main pages without smoke', function () {
    $user = User::factory()->create();

    $routes = [
        '/',
        '/dashboard',
        '/races',
        '/predictions',
        '/standings',
        '/drivers',
        '/teams',
        '/circuits',
        '/profile',
    ];

    $pages = visit($routes)
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $pages->assertNoSmoke();
});

it('can visit all main pages in dark mode without smoke', function () {
    $user = User::factory()->create();

    $routes = [
        '/',
        '/dashboard',
        '/races',
        '/predictions',
        '/standings',
        '/drivers',
        '/teams',
        '/circuits',
        '/profile',
    ];

    $pages = visit($routes)
        ->actingAs($user)
        ->on()->desktop()
        ->inDarkMode();

    $pages->assertNoSmoke();
});

it('can visit all main pages on mobile without smoke', function () {
    $user = User::factory()->create();

    $routes = [
        '/',
        '/dashboard',
        '/races',
        '/predictions',
        '/standings',
        '/drivers',
        '/teams',
        '/circuits',
        '/profile',
    ];

    $pages = visit($routes)
        ->actingAs($user)
        ->on()->mobile()
        ->inLightMode();

    $pages->assertNoSmoke();
});

it('can visit all main pages on tablet without smoke', function () {
    $user = User::factory()->create();

    $routes = [
        '/',
        '/dashboard',
        '/races',
        '/predictions',
        '/standings',
        '/drivers',
        '/teams',
        '/circuits',
        '/profile',
    ];

    $pages = visit($routes)
        ->actingAs($user)
        ->on()->tablet()
        ->inLightMode();

    $pages->assertNoSmoke();
});
