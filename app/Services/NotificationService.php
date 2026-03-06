<?php

namespace App\Services;

use App\Events\NotificationReceived;
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
        User::whereHas('predictions', function ($query) use ($race) {
            $query->where('race_id', $race->id)
                ->where('type', 'race');
        })->chunkById(500, function ($users) use ($race) {
            Notification::send($users, new RaceResultsAvailable($race));

            // Dispatch real-time events for each user in the chunk
            foreach ($users as $user) {
                event(new NotificationReceived($user, [
                    'type' => 'race_results_available',
                    'race_id' => $race->id,
                    'race_name' => $race->race_name,
                    'display_name' => $race->display_name,
                    'season' => $race->season,
                    'round' => $race->round,
                    'message' => "Race results for {$race->display_name} are now available",
                    'action_url' => "/{$race->season}/race/{$race->id}",
                ]));
            }
        });
    }

    /**
     * Send prediction scored notification to a specific user.
     */
    public function sendPredictionScoredNotification(Prediction $prediction, int $score, float $accuracy): void
    {
        $prediction->user->notify(new PredictionScored($prediction, $score, $accuracy));

        // Dispatch real-time event
        event(new NotificationReceived($prediction->user, [
            'type' => 'prediction_scored',
            'prediction_id' => $prediction->id,
            'prediction_type' => $prediction->type,
            'season' => $prediction->season,
            'score' => $score,
            'accuracy' => $accuracy,
            'race_name' => $prediction->race?->race_name,
            'display_name' => $prediction->race?->display_name,
            'message' => sprintf(
                'Your %s prediction for %s has been scored: %d points (%.1f%% accuracy)',
                $prediction->type,
                $prediction->race?->display_name ?? "{$prediction->season} season",
                $score,
                $accuracy
            ),
            'action_url' => '/predictions',
        ]));
    }

    /**
     * Send prediction deadline reminder to all users.
     */
    public function sendPredictionDeadlineReminder(Races $race, string $deadlineType = 'qualifying'): void
    {
        User::chunkById(500, function ($users) use ($race, $deadlineType) {
            Notification::send($users, new PredictionDeadlineReminder($race, $deadlineType));
        });
    }

    /**
     * Send prediction deadline reminder to users who haven't submitted race predictions yet
     * and who haven't already received this reminder for this race.
     */
    public function sendPredictionDeadlineReminderToNonPredictors(Races $race, string $deadlineType = 'qualifying'): void
    {
        User::whereDoesntHave('predictions', function ($query) use ($race) {
            $query->where('race_id', $race->id)
                ->where('type', 'race');
        })
            ->whereNotReceivedDeadlineReminder($race, $deadlineType)
            ->chunkById(500, function ($users) use ($race, $deadlineType) {
                Notification::send($users, new PredictionDeadlineReminder($race, $deadlineType));
            });
    }

    /**
     * Send sprint deadline reminder to users who haven't submitted sprint predictions for this race
     * and who haven't already received this reminder.
     */
    public function sendSprintDeadlineReminderToNonPredictors(Races $race): void
    {
        User::whereDoesntHave('predictions', function ($query) use ($race) {
            $query->where('race_id', $race->id)
                ->where('type', 'sprint');
        })
            ->whereNotReceivedDeadlineReminder($race, 'sprint')
            ->chunkById(500, function ($users) use ($race) {
                Notification::send($users, new PredictionDeadlineReminder($race, 'sprint'));
            });
    }

    /**
     * Send preseason prediction deadline reminder to all users.
     * Uses the first race of the season when available so the reminder matches that deadline.
     */
    public function sendPreseasonDeadlineReminder(int $season): void
    {
        $race = Races::getFirstRaceOfSeason($season);
        if ($race === null) {
            $race = new Races([
                'season' => $season,
                'race_name' => "{$season} Season",
                'round' => 0,
            ]);
        }

        User::chunkById(500, function ($users) use ($race) {
            Notification::send($users, new PredictionDeadlineReminder($race, 'preseason'));
        });
    }

    /**
     * Send preseason deadline reminder only to users who haven't submitted a preseason prediction
     * and who haven't already received this reminder for this season.
     */
    public function sendPreseasonDeadlineReminderToNonPredictors(int $season): void
    {
        $race = Races::getFirstRaceOfSeason($season);
        if ($race === null) {
            $race = new Races([
                'season' => $season,
                'race_name' => "{$season} Season",
                'round' => 0,
            ]);
        }

        User::whereDoesntHave('predictions', function ($query) use ($season) {
            $query->where('season', $season)->where('type', 'preseason');
        })
            ->whereNotReceivedDeadlineReminder($race, 'preseason')
            ->chunkById(500, function ($users) use ($race) {
                Notification::send($users, new PredictionDeadlineReminder($race, 'preseason'));
            });
    }

    /**
     * Send midseason prediction deadline reminder to all users.
     */
    public function sendMidseasonDeadlineReminder(int $season): void
    {
        $race = new Races([
            'season' => $season,
            'race_name' => "{$season} Midseason",
            'round' => 0,
        ]);

        User::chunkById(500, function ($users) use ($race) {
            Notification::send($users, new PredictionDeadlineReminder($race, 'midseason'));
        });
    }

    /**
     * Send midseason deadline reminder only to users who haven't submitted a midseason prediction
     * and who haven't already received this reminder for this season.
     */
    public function sendMidseasonDeadlineReminderToNonPredictors(int $season): void
    {
        $race = new Races([
            'season' => $season,
            'race_name' => "{$season} Midseason",
            'round' => 0,
        ]);

        User::whereDoesntHave('predictions', function ($query) use ($season) {
            $query->where('season', $season)->where('type', 'midseason');
        })
            ->whereNotReceivedDeadlineReminder($race, 'midseason')
            ->chunkById(500, function ($users) use ($race) {
                Notification::send($users, new PredictionDeadlineReminder($race, 'midseason'));
            });
    }
}
