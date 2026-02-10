<?php

/**
 * User model security and mass-assignment tests.
 */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('mass-assigning is_admin via create is rejected', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'hacker@example.com',
        'password' => 'secret',
        'is_admin' => true,
    ]);

    $user->refresh();
    expect($user->is_admin)->toBeFalse();
});

test('mass-assigning is_admin via update is rejected', function () {
    $user = User::factory()->create();

    $user->update(['is_admin' => true]);

    $user->refresh();
    expect($user->is_admin)->toBeFalse();
});
