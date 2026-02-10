<?php

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin seeder creates admin user from env variables', function () {
    config([
        'admin.promotable_admin_email' => 'admin@example.com',
        'admin.admin_name' => 'Test Admin',
        'admin.admin_password' => 'secure-password',
    ]);

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();
    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('Test Admin')
        ->and($admin->is_admin)->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull();
});

test('admin seeder updates existing user', function () {
    config([
        'admin.promotable_admin_email' => 'admin@example.com',
        'admin.admin_name' => 'Updated Name',
        'admin.admin_password' => 'new-password',
    ]);

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
});

test('admin seeder skips when ADMIN_PASSWORD not set', function () {
    config([
        'admin.promotable_admin_email' => 'admin@example.com',
        'admin.admin_name' => 'Test Admin',
        'admin.admin_password' => null,
    ]);

    $this->seed(AdminSeeder::class);

    expect(User::count())->toBe(0);
});

test('admin seeder uses default name when ADMIN_NAME not set', function () {
    config([
        'admin.promotable_admin_email' => 'admin@example.com',
        'admin.admin_name' => null,
        'admin.admin_password' => 'secure-password',
    ]);

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();
    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('Admin')
        ->and($admin->is_admin)->toBeTrue();
});

test('admin seeder skips when ADMIN_EMAIL not set', function () {
    config(['admin.promotable_admin_email' => null]);

    $this->seed(AdminSeeder::class);

    expect(User::count())->toBe(0);
});
