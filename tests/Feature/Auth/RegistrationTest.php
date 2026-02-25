<?php

use App\Mail\NewUserRegistered;
use App\Mail\VerifyEmailMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/register');

    $response->assertOk();
});

test('register page returns 200 with data-appearance and shared layout branding', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/register');

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('data-appearance=');
    expect($content)->toContain('F1 Predictor');
    expect($content)->toContain('min-h-screen');
    expect($content)->toContain('dark:bg-zinc-900');
    expect($content)->not->toContain('Whoops');
    expect($content)->not->toContain('500 | Server Error');
});

test('new users can register', function () {
    /** @var \Tests\TestCase $this */
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

test('verification email is sent on registration when user must verify email', function () {
    /** @var \Tests\TestCase $this */
    if (! in_array(\Illuminate\Contracts\Auth\MustVerifyEmail::class, class_implements(User::class) ?? [])) {
        $this->markTestSkipped('User model does not implement MustVerifyEmail; verification not sent.');
    }

    Mail::fake();

    Volt::test('auth.register')
        ->set('name', 'Verify Me')
        ->set('email', 'verify@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $user = User::where('email', 'verify@example.com')->first();
    expect($user)->not->toBeNull();

    Mail::assertSent(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user) {
        return $mail->hasTo($user->getEmailForVerification());
    });
});

test('admin receives NewUserRegistered email when ADMIN_EMAIL is set', function () {
    Mail::fake();
    $adminEmail = 'admin@example.com';
    config(['admin.promotable_admin_email' => $adminEmail]);

    Volt::test('auth.register')
        ->set('name', 'Newbie User')
        ->set('email', 'newbie@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $user = User::where('email', 'newbie@example.com')->first();
    expect($user)->not->toBeNull();

    Mail::assertSent(NewUserRegistered::class, function (NewUserRegistered $mail) use ($adminEmail, $user) {
        return $mail->hasTo($adminEmail)
            && $mail->user->is($user)
            && $mail->user->name === 'Newbie User';
    });
});

test('admin does not receive NewUserRegistered when ADMIN_EMAIL is not set', function () {
    Mail::fake();
    config(['admin.promotable_admin_email' => null]);

    Volt::test('auth.register')
        ->set('name', 'No Admin Notify')
        ->set('email', 'noadmin@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    Mail::assertNotSent(NewUserRegistered::class);
});
