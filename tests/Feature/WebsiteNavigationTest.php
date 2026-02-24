<?php

use App\Models\Prediction;
use App\Models\User;
use App\Services\F1ApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Auth redirect tests (guest → /login) live in RoutesTest (authentication required routes block).

test('home page loads successfully', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/');
    $response->assertOk();
});

test('authenticated user can access dashboard analytics settings and predictions', function () {
    /** @var \Tests\TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get('/dashboard')->assertOk();
    $this->get('/analytics')->assertOk();
    $this->get('/settings/profile')->assertOk();
    $this->get('/settings/password')->assertOk();
    $this->get('/settings/appearance')->assertOk();
    $this->get(route('predict.create'))->assertOk();
    $this->get('/predictions')->assertOk();

    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'status' => 'draft',
    ]);
    $this->get(route('predictions.edit', $prediction))->assertOk();
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
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/dashboard');
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

test('sidebar shows Countries navigation link when country page is ready', function () {
    /** @var \Tests\TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Countries', false);
    $response->assertSee(route('countries'), false);
});

test('home page shows Countries navigation card when country page is ready', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('View Countries', false);
});
