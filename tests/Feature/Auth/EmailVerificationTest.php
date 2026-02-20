<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Email verification is disabled (no mail server). Verification-specific tests skipped.
// Re-enable and restore tests below when verification is turned back on.

test('any authenticated user can access dashboard without email verification', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

test('any authenticated user can access predict create without email verification', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('predict.create'));

    $response->assertSuccessful();
});
