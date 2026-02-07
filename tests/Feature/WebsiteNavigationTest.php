<?php

use App\Models\User;
use App\Services\F1ApiService;

test('home page loads successfully', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

test('analytics page requires authentication', function () {
    $response = $this->get('/analytics');
    $response->assertRedirect(); // Should redirect to login since auth is required
});

test('analytics page loads for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/analytics');
    $response->assertStatus(200);
});

test('admin pages require authentication', function () {
    $response = $this->get('/admin/dashboard');
    $response->assertRedirect(); // Should redirect to login
});

test('admin dashboard loads for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/dashboard');
    $response->assertStatus(200);
});

test('predictions page components work', function () {
    $response = $this->get('/');
    $response->assertStatus(200);

    // Check if Livewire components are present
    $response->assertSee('livewire');
});

test('api endpoints are accessible', function () {
    $this->mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')->andReturn([]);
    });

    $response = $this->get('/api/f1/races/2024');
    $response->assertSuccessful();
});
