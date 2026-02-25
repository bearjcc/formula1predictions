<?php

use App\Mail\VerifyEmailMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Email verification is disabled (no mail server). Verification-specific tests skipped.
// Re-enable and restore tests below when verification is turned back on.

test('unverified user can resend verification from profile without 500', function () {
    Mail::fake();
    $user = User::factory()->unverified()->create(['email' => 'legacy@example.com']);

    $response = $this->actingAs($user)->get(route('settings.profile'));
    $response->assertOk();

    $component = \Livewire\Volt\Volt::test('settings.profile');
    $component->call('resendVerificationNotification');

    $component->assertHasNoErrors();
    Mail::assertSent(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('any authenticated user can access dashboard without email verification', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

test('any authenticated user can access predict create without email verification', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('predict.create'));

    $response->assertSuccessful();
});
