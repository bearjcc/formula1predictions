<?php

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested for verified user', function () {
    Mail::fake();

    $user = User::factory()->create(); // verified by default

    Volt::test('auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Mail::assertSent(ResetPasswordMail::class, function (ResetPasswordMail $mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('reset password link is not sent for unverified user', function () {
    Mail::fake();

    $user = User::factory()->unverified()->create();

    Volt::test('auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Mail::assertNotSent(ResetPasswordMail::class);
});

test('reset password screen can be rendered', function () {
    Mail::fake();

    $user = User::factory()->create();

    Volt::test('auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    $token = null;
    Mail::assertSent(ResetPasswordMail::class, function (ResetPasswordMail $mail) use (&$token) {
        $token = $mail->token;

        return true;
    });
    $this->assertNotNull($token);

    $response = $this->get('/reset-password/'.$token.'?email='.urlencode($user->email));
    $response->assertStatus(200);
});

test('password can be reset with valid token', function () {
    Mail::fake();

    $user = User::factory()->create();

    Volt::test('auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    $token = null;
    Mail::assertSent(ResetPasswordMail::class, function (ResetPasswordMail $mail) use (&$token) {
        $token = $mail->token;

        return true;
    });
    $this->assertNotNull($token);

    $response = Volt::test('auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('resetPassword');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('login', absolute: false));
});
