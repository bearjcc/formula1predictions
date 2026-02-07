<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('dashboard shows dynamic stats for authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee('Total Predictions', false);
    $response->assertSee('Prediction Accuracy', false);
    $response->assertSee('Total Points', false);
    $response->assertSee('Current Rank', false);
    $response->assertSee('Quick Actions', false);
});
