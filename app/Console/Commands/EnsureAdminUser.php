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
        $password = config('admin.admin_password');
        $madeChanges = false;

        if (empty($email)) {
            $this->warn('ADMIN_EMAIL is not set. Skipping admin creation.');
            $this->comment('Set ADMIN_EMAIL (and optionally ADMIN_NAME, ADMIN_PASSWORD) to auto-create admin on deploy.');

            return Command::SUCCESS;
        }

        /** @var \App\Models\User|null $user */
        $user = User::where('email', $email)->first();

        if (! $user) {
            if (empty($password)) {
                $this->warn('ADMIN_PASSWORD is not set. Cannot create admin without an explicit password.');
                $this->comment('Set ADMIN_PASSWORD in production env to auto-create admin on deploy.');

                return Command::SUCCESS;
            }

            $user = User::create([
                'email' => $email,
                'name' => $name,
                // Let the User model's "hashed" cast handle encryption so it stays
                // consistent with normal registrations and password updates.
                'password' => $password,
                'email_verified_at' => now(),
            ]);
            $user->forceFill(['is_admin' => true])->save();

            $this->info("Created admin user {$email}.");
            $this->warn('Remember to change this password after first login.');

            return Command::SUCCESS;
        }

        if (! $user->is_admin) {
            $user->forceFill(['is_admin' => true]);
            $madeChanges = true;
        }

        // If an ADMIN_PASSWORD is configured and it does not match the current
        // password hash, sync it so deploy-time credentials always reflect
        // the environment variables.
        if (! empty($password) && ! Hash::check($password, $user->password)) {
            $user->password = $password;
            $madeChanges = true;
        }

        if ($madeChanges) {
            $user->save();

            $this->info("Admin user {$email} already existed; credentials synced with environment.");

            return Command::SUCCESS;
        }

        $this->info("Admin user {$email} already exists. No changes made.");

        return Command::SUCCESS;
    }
}
