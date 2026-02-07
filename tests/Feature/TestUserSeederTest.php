<?php

use App\Models\User;
use Database\Seeders\TestUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt as LivewireVolt;

uses(RefreshDatabase::class);

test('test user seeder creates expected users', function () {
    $this->seed(TestUserSeeder::class);

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue()
        ->and(User::where('email', 'admin@example.com')->exists())->toBeTrue()
        ->and(User::where('email', 'unverified@example.com')->exists())->toBeTrue();

    $testUser = User::where('email', 'test@example.com')->first();
    expect($testUser->email_verified_at)->not->toBeNull();

    $adminUser = User::where('email', 'admin@example.com')->first();
    expect($adminUser->email_verified_at)->not->toBeNull();
    expect($adminUser->hasRole('admin'))->toBeTrue();

    $unverifiedUser = User::where('email', 'unverified@example.com')->first();
    expect($unverifiedUser->email_verified_at)->toBeNull();
});

test('test user can log in and access dashboard', function () {
    $this->seed(TestUserSeeder::class);

    $response = LivewireVolt::test('auth.login')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('login');

    $response->assertHasNoErrors()->assertRedirect();
    $this->assertAuthenticated();
});

test('admin user can access admin dashboard', function () {
    $this->seed(TestUserSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertOk();
});

test('unverified user can log in', function () {
    $this->seed(TestUserSeeder::class);

    $response = LivewireVolt::test('auth.login')
        ->set('email', 'unverified@example.com')
        ->set('password', 'password')
        ->call('login');

    $response->assertHasNoErrors()->assertRedirect();
    $this->assertAuthenticated();
});
