<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('analytics page renders with red brand focus ring on season select', function () {
    /** @var \Tests\TestCase $this */
    /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->createOne();

    actingAs($user);

    $response = get('/analytics');

    $response->assertOk();
    $response->assertSee('focus:ring-red-600', false);
});

