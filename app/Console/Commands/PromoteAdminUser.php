<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteAdminUser extends Command
{
    protected $signature = 'app:promote-admin
                            {email? : Email of the user to promote (optional if ADMIN_EMAIL is set)}';

    protected $description = 'Promote a user to admin by email. Idempotent; safe to run after deploy.';

    public function handle(): int
    {
        $email = $this->argument('email') ?? config('admin.promotable_admin_email');

        if (empty($email)) {
            $this->error('Provide an email: php artisan app:promote-admin you@example.com');
            $this->comment('Or set ADMIN_EMAIL in .env and run: php artisan app:promote-admin');

            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");
            $this->comment('Create the account (e.g. register) first, then run this command again.');

            return Command::FAILURE;
        }

        if ($user->is_admin) {
            $this->info("User {$email} is already an admin.");

            return Command::SUCCESS;
        }

        $user->forceFill(['is_admin' => true])->save();
        $this->info("Promoted {$email} to admin.");

        return Command::SUCCESS;
    }
}
