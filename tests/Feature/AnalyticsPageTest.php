<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('analytics page renders with red brand focus ring on season select', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/analytics');

    $response->assertOk();
    $response->assertSee('focus:ring-red-600', false);
});

