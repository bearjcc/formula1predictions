<?php

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin seeder creates admin user from env variables', function () {
    config(['admin.promotable_admin_email' => 'admin@example.com']);
    putenv('ADMIN_NAME=Test Admin');
    putenv('ADMIN_PASSWORD=secure-password');

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();
    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('Test Admin')
        ->and($admin->is_admin)->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull();

    // Clean up env
    putenv('ADMIN_NAME');
    putenv('ADMIN_PASSWORD');
});

test('admin seeder updates existing user', function () {
    config(['admin.promotable_admin_email' => 'admin@example.com']);
    putenv('ADMIN_NAME=Updated Name');
    putenv('ADMIN_PASSWORD=new-password');

    // Create user first
    User::factory()->create([
        'email' => 'admin@example.com',
        'name' => 'Old Name',
        'is_admin' => false,
    ]);

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();
    expect($admin->name)->toBe('Updated Name')
        ->and($admin->is_admin)->toBeTrue();

    // Clean up env
    putenv('ADMIN_NAME');
    putenv('ADMIN_PASSWORD');
});

test('admin seeder uses defaults when env not set', function () {
    config(['admin.promotable_admin_email' => 'admin@example.com']);

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();
    expect($admin->name)->toBe('Admin')
        ->and($admin->is_admin)->toBeTrue();
});

test('admin seeder skips when ADMIN_EMAIL not set', function () {
    config(['admin.promotable_admin_email' => null]);

    $this->seed(AdminSeeder::class);

    expect(User::count())->toBe(0);
});
