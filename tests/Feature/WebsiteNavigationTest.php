<?php

use App\Models\User;
use App\Services\F1ApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('home page loads successfully', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/');
    $response->assertOk();
});

test('analytics page requires authentication', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/analytics');
    $response->assertRedirect('/login');
});

test('analytics page loads for authenticated user', function () {
    /** @var \Tests\TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/analytics');
    $response->assertOk();
});

test('admin dashboard requires authentication and admin role', function () {
    /** @var \Tests\TestCase $this */
    // Guest redirected to login
    $this->get('/admin/dashboard')->assertRedirect('/login');

    // Regular user should be forbidden or redirected away (admin middleware)
    /** @var User $user */
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);
    $response = $this->get('/admin/dashboard');
    $response->assertForbidden();
});

test('admin dashboard loads for admin user', function () {
    /** @var \Tests\TestCase $this */
    /** @var User $admin */
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $response->assertOk();
});

test('predictions landing is reachable for authenticated users', function () {
    /** @var \Tests\TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/predictions');
    $response->assertOk();
});

test('api race endpoints are accessible with mocked F1 API', function () {
    /** @var \Tests\TestCase $this */
    $this->mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')->andReturn([]);
    });

    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response2024 = $this->get('/api/f1/races/2024');
    $response2024->assertOk();

    // Keep this in sync with config('f1.current_season')
    $responseCurrent = $this->get('/api/f1/races/2026');
    $responseCurrent->assertOk();
});
