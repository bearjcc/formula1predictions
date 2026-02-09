<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin promotion email
    |--------------------------------------------------------------------------
    | Set ADMIN_EMAIL in .env to the address that should be promoted to admin.
    | Run: php artisan app:promote-admin
    | (Or pass the email: php artisan app:promote-admin you@example.com)
    */
    'promotable_admin_email' => env('ADMIN_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Admin seeder defaults
    |--------------------------------------------------------------------------
    | These values are used by the AdminSeeder when creating or updating the
    | initial admin user. They default from environment variables but should
    | be accessed via config() in application code.
    */
    'admin_name' => env('ADMIN_NAME', 'Admin'),
    'admin_password' => env('ADMIN_PASSWORD', 'password'),
];
