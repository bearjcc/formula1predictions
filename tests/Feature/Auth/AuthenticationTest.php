<?php

use App\Models\User;
use Livewire\Volt\Volt as LivewireVolt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertOk();
});

test('login page returns 200 with data-appearance and shared layout branding', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('data-appearance=');
    expect($content)->toContain('Predict race outcomes.');
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

test('auth layout has shared body classes and blocking appearance script in head', function () {
    $response = $this->get('/login');
    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('min-h-screen');
    expect($content)->toContain('bg-white');
    expect($content)->toContain('dark:bg-zinc-900');
    expect($content)->toContain("classList.toggle('dark'");
});

test('dark appearance sets html dark class for consistent background', function () {
    $response = $this->withSession(['appearance' => 'dark'])->get('/login');
    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('class="dark"');
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
