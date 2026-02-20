<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertOk();
});

test('register page returns 200 with data-appearance and shared layout branding', function () {
    $response = $this->get('/register');

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('data-appearance=');
    expect($content)->toContain('Predict race outcomes.');
    expect($content)->toContain('min-h-screen');
    expect($content)->toContain('dark:bg-zinc-900');
});

test('new users can register', function () {
    $response = Volt::test('auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('verification email is sent on registration', function () {
    Notification::fake();

    Volt::test('auth.register')
        ->set('name', 'Verify Me')
        ->set('email', 'verify@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $user = User::where('email', 'verify@example.com')->first();
    expect($user)->not->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});
