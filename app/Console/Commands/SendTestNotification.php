<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendTestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test {--user= : User ID to send notification to} {--type=race : Type of notification (race, prediction)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification to test the real-time notification system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user');
        $type = $this->option('type');

        if ($userId) {
            $user = User::find($userId);
            if (! $user) {
                $this->error("User with ID {$userId} not found.");

                return 1;
            }
        } else {
            $user = User::first();
            if (! $user) {
                $this->error('No users found in the database.');

                return 1;
            }
        }

        $this->info("Sending test notification to user: {$user->name} ({$user->email})");

        $notificationService = new NotificationService;

        if ($type === 'race') {
            // Create a test race
            $race = \App\Models\Races::factory()->create([
                'race_name' => 'Test Grand Prix',
                'season' => 2024,
                'round' => 1,
            ]);

            // Create a test prediction for the user
            \App\Models\Prediction::factory()->create([
                'user_id' => $user->id,
                'race_id' => $race->id,
                'type' => 'race',
            ]);

            $notificationService->sendRaceResultsAvailableNotification($race);
            $this->info("Sent race results available notification for: {$race->display_name}");
        } elseif ($type === 'prediction') {
            // Create a test race and prediction
            $race = \App\Models\Races::factory()->create([
                'race_name' => 'Test Grand Prix',
                'season' => 2024,
                'round' => 1,
            ]);

            $prediction = \App\Models\Prediction::factory()->create([
                'user_id' => $user->id,
                'race_id' => $race->id,
                'type' => 'race',
            ]);

            $notificationService->sendPredictionScoredNotification($prediction, 85, 75.5);
            $this->info('Sent prediction scored notification with 85 points and 75.5% accuracy');
        } else {
            $this->error("Invalid notification type: {$type}. Use 'race' or 'prediction'.");

            return 1;
        }

        $this->info('Test notification sent successfully!');
        $this->info('Check the notification dropdown in the UI or visit /notifications to see the notification.');

        return 0;
    }
}
