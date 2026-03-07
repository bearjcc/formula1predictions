<?php

namespace App\Console\Commands;

use App\Mail\BuildSuccessNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyBuildSuccess extends Command
{
    protected $signature = 'app:notify-build-success';

    protected $description = 'Send admin a one-line email confirming build success and that mail is configured. Only runs when ADMIN_EMAIL is set.';

    public function handle(): int
    {
        $adminEmail = config('admin.promotable_admin_email') ?: getenv('ADMIN_EMAIL') ?: null;

        if (empty($adminEmail)) {
            return Command::SUCCESS;
        }

        try {
            Mail::to($adminEmail)->send(new BuildSuccessNotification());
        } catch (\Throwable $e) {
            $this->error('Build success notification failed: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
