<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;

it('can visit dashboard and see predictions', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('can switch to dark mode on dashboard', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->desktop()
        ->inDarkMode();

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
});

it('can view dashboard on mobile device', function () {
    $user = User::factory()->create();

    $page = visit('/dashboard')
        ->actingAs($user)
        ->on()->mobile()
        ->inLightMode();

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
});

it('can navigate through main pages without errors', function () {
    $user = User::factory()->create();

    $pages = visit(['/', '/dashboard', '/races', '/predictions'])
        ->actingAs($user)
        ->on()->desktop();

    $pages->assertNoSmoke();
});
