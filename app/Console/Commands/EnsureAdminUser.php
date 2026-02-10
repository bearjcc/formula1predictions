<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureAdminUser extends Command
{
    protected $signature = 'app:ensure-admin-user';

    protected $description = 'Ensure the ADMIN_EMAIL user exists and is an admin. Safe to run on every deploy.';

    public function handle(): int
    {
        $email = config('admin.promotable_admin_email');
        $name = config('admin.admin_name') ?? 'Admin';
        $password = config('admin.admin_password') ?? 'password';

        if (empty($email)) {
            $this->error('ADMIN_EMAIL is not set. Skipping admin creation.');
            $this->comment('Set ADMIN_EMAIL (and optionally ADMIN_NAME, ADMIN_PASSWORD) then re-run this command.');

            return Command::FAILURE;
        }

        /** @var \App\Models\User|null $user */
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'email' => $email,
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $user->forceFill(['is_admin' => true])->save();

            $this->info("Created admin user {$email}.");
            $this->warn('Remember to change this password after first login.');

            return Command::SUCCESS;
        }

        if (! $user->is_admin) {
            $user->forceFill(['is_admin' => true])->save();
            $this->info("User {$email} already existed; promoted to admin.");

            return Command::SUCCESS;
        }

        $this->info("Admin user {$email} already exists. No changes made.");

        return Command::SUCCESS;
    }
}
