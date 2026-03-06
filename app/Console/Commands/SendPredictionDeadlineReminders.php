<?php

namespace App\Console\Commands;

use App\Models\Races;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPredictionDeadlineReminders extends Command
{
    protected $signature = 'reminders:send-deadline';

    protected $description = 'Send prediction deadline reminders (~72h before deadline) to non-predictors only. Includes catch-up for past 72h window (e.g. race 1).';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $season = config('f1.current_season');
        $now = Carbon::now();

        $sent = 0;

        #region Preseason
        $preseasonDeadline = Races::getPreseasonDeadlineForSeason($season);
        if ($preseasonDeadline !== null) {
            $windowStart = $preseasonDeadline->copy()->subHours(72);
            $windowEnd = $preseasonDeadline->copy()->addHours(24);
            if ($now->between($windowStart, $windowEnd)) {
                $this->notificationService->sendPreseasonDeadlineReminderToNonPredictors($season);
                $this->line('Queued preseason deadline reminders for season ' . $season);
                $sent++;
            }
        }
        #endregion

        #region Races and sprints
        $races = Races::where('season', $season)
            ->whereNotNull('qualifying_start')
            ->orderBy('round')
            ->get();

        foreach ($races as $race) {
            $deadline = $race->getRacePredictionDeadline();
            if ($deadline === null) {
                continue;
            }
            $windowStart = $deadline->copy()->subHours(72);
            $windowEnd = $deadline->copy()->addHours(24);
            if ($now->between($windowStart, $windowEnd)) {
                $this->notificationService->sendPredictionDeadlineReminderToNonPredictors($race, 'qualifying');
                $this->line("Queued race deadline reminders for {$race->display_name}");
                $sent++;
            }

            if ($race->hasSprint() && $race->sprint_qualifying_start !== null) {
                $sprintDeadline = $race->getSprintPredictionDeadline();
                if ($sprintDeadline !== null) {
                    $sprintWindowStart = $sprintDeadline->copy()->subHours(72);
                    $sprintWindowEnd = $sprintDeadline->copy()->addHours(24);
                    if ($now->between($sprintWindowStart, $sprintWindowEnd)) {
                        $this->notificationService->sendSprintDeadlineReminderToNonPredictors($race);
                        $this->line("Queued sprint deadline reminders for {$race->display_name}");
                        $sent++;
                    }
                }
            }
        }
        #endregion

        if ($sent === 0) {
            $this->info('No reminder windows active.');
        } else {
            $this->info("Scheduled {$sent} reminder batch(es). Emails will be sent via the queue.");
        }

        return Command::SUCCESS;
    }
}
