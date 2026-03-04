<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetBotAndLegacyPredictions extends Command
{
    protected $signature = 'app:reset-bot-legacy-predictions';

    protected $description = 'One-time cleanup: drop bot/legacy predictions with inconsistent driver IDs and reseed them using current seeders.';

    private const JOB_NAME = 'reset_bot_legacy_predictions_to_driver_ids';

    public function handle(): int
    {
        if (! Schema::hasTable('one_time_jobs')) {
            $this->warn('one_time_jobs table missing. Run migrations first.');

            return Command::SUCCESS;
        }

        $alreadyRan = DB::table('one_time_jobs')->where('name', self::JOB_NAME)->exists();
        if ($alreadyRan) {
            $this->info('Bot/legacy prediction reset already ran. Skipping.');

            return Command::SUCCESS;
        }

        $botEmails = config('f1.bot_emails', []);

        $legacyEmails = [
            'bearjcc@example.com',
            'sunny@example.com',
            'ccaswell@example.com',
            'chatgpt@example.com',
        ];

        $targetEmails = array_values(array_unique(array_merge($botEmails, $legacyEmails)));

        if ($targetEmails === []) {
            $this->info('No bot/legacy emails configured; nothing to reset.');

            DB::table('one_time_jobs')->insert([
                'name' => self::JOB_NAME,
                'run_at' => now(),
            ]);

            return Command::SUCCESS;
        }

        $userIds = DB::table('users')
            ->whereIn('email', $targetEmails)
            ->pluck('id')
            ->all();

        if ($userIds === []) {
            $this->info('No bot/legacy users found; nothing to delete.');
        } else {
            $deleted = DB::table('predictions')
                ->whereIn('user_id', $userIds)
                ->delete();

            $this->info("Deleted {$deleted} predictions for bot/legacy users.");
        }

        // Reseed legacy historical predictions (markdown/CSV fixtures in repo)
        $this->info('Re-importing historical predictions via legacy pipeline...');
        $this->call('legacy:import-historical-predictions');

        // Reseed all algorithmic bots using the current, driverId-based seeders
        $this->info('Seeding algorithmic bot predictions with canonical driver IDs...');
        $this->call('bots:seed');

        DB::table('one_time_jobs')->insert([
            'name' => self::JOB_NAME,
            'run_at' => now(),
        ]);

        $this->info('Bot/legacy prediction reset completed. Flag set so this will not run again.');

        return Command::SUCCESS;
    }
}

