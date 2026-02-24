<?php

use App\Models\User;
use Database\Seeders\TestUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt as LivewireVolt;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class)->group('slow');

test('test user seeder creates expected users', function () {
    seed(TestUserSeeder::class);

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
    seed(TestUserSeeder::class);

    $response = LivewireVolt::test('auth.login')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('login');

    $response->assertHasNoErrors()->assertRedirect();
    expect(Auth::check())->toBeTrue();
});

test('admin user can access admin dashboard', function () {
    seed(TestUserSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();

    $response = actingAs($admin)->get('/admin/dashboard');

    $response->assertOk();
});

test('unverified user can log in', function () {
    seed(TestUserSeeder::class);

    $response = LivewireVolt::test('auth.login')
        ->set('email', 'unverified@example.com')
        ->set('password', 'password')
        ->call('login');

    $response->assertHasNoErrors()->assertRedirect();
    expect(Auth::check())->toBeTrue();
});
