<?php

use App\Models\User;
use Livewire\Volt\Volt as LivewireVolt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('auth pages use data-appearance for theme consistency', function () {
    $response = $this->get('/login');
    $response->assertOk();
    expect($response->getContent())->toContain('data-appearance="system"');
});

test('auth pages respect session appearance preference', function () {
    $response = $this->withSession(['appearance' => 'dark'])->get('/login');
    $response->assertOk();
    expect($response->getContent())->toContain('data-appearance="dark"');

    $response = $this->withSession(['appearance' => 'light'])->get('/register');
    $response->assertOk();
    expect($response->getContent())->toContain('data-appearance="light"');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login');

    $response->assertHasErrors('email');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');

    $this->assertGuest();
});
