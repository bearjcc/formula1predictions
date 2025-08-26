<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Notifications\PredictionDeadlineReminder;
use App\Notifications\PredictionScored;
use App\Notifications\RaceResultsAvailable;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send race results available notification to all users who made predictions for this race.
     */
    public function sendRaceResultsAvailableNotification(Races $race): void
    {
        $users = User::whereHas('predictions', function ($query) use ($race) {
            $query->where('race_id', $race->id)
                  ->where('type', 'race');
        })->get();

        Notification::send($users, new RaceResultsAvailable($race));
    }

    /**
     * Send prediction scored notification to a specific user.
     */
    public function sendPredictionScoredNotification(Prediction $prediction, int $score, float $accuracy): void
    {
        $prediction->user->notify(new PredictionScored($prediction, $score, $accuracy));
    }

    /**
     * Send prediction deadline reminder to all users.
     */
    public function sendPredictionDeadlineReminder(Races $race, string $deadlineType = 'qualifying'): void
    {
        $users = User::all();
        Notification::send($users, new PredictionDeadlineReminder($race, $deadlineType));
    }

    /**
     * Send prediction deadline reminder to users who haven't submitted predictions yet.
     */
    public function sendPredictionDeadlineReminderToNonPredictors(Races $race, string $deadlineType = 'qualifying'): void
    {
        $users = User::whereDoesntHave('predictions', function ($query) use ($race) {
            $query->where('race_id', $race->id)
                  ->where('type', 'race');
        })->get();

        Notification::send($users, new PredictionDeadlineReminder($race, $deadlineType));
    }

    /**
     * Send preseason prediction deadline reminder to all users.
     */
    public function sendPreseasonDeadlineReminder(int $season): void
    {
        $users = User::all();
        
        // Create a dummy race object for the notification
        $race = new Races([
            'season' => $season,
            'race_name' => "{$season} Season",
            'round' => 0,
        ]);

        Notification::send($users, new PredictionDeadlineReminder($race, 'preseason'));
    }

    /**
     * Send midseason prediction deadline reminder to all users.
     */
    public function sendMidseasonDeadlineReminder(int $season): void
    {
        $users = User::all();
        
        // Create a dummy race object for the notification
        $race = new Races([
            'season' => $season,
            'race_name' => "{$season} Midseason",
            'round' => 0,
        ]);

        Notification::send($users, new PredictionDeadlineReminder($race, 'midseason'));
    }
}
