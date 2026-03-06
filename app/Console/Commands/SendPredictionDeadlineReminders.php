<?php

namespace App\Console\Commands;

use App\Models\Races;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPredictionDeadlineReminders extends Command
{
    protected $signature = 'reminders:send-deadline
                            {--force : Skip time-window check and send all currently open deadlines immediately}';

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
        $force = (bool) $this->option('force');

        $sent = 0;

        #region Preseason
        $preseasonDeadline = Races::getPreseasonDeadlineForSeason($season);
        if ($preseasonDeadline !== null) {
            $inWindow = $now->between(
                $preseasonDeadline->copy()->subHours(72),
                $preseasonDeadline->copy()->addHours(24)
            );
            // --force: send if deadline hasn't passed yet (predictions still open)
            $shouldSend = $force ? $now->lt($preseasonDeadline) : $inWindow;
            if ($shouldSend) {
                $this->notificationService->sendPreseasonDeadlineReminderToNonPredictors($season);
                $this->line('Sent preseason deadline reminders for season ' . $season);
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
            $inWindow = $now->between(
                $deadline->copy()->subHours(72),
                $deadline->copy()->addHours(24)
            );
            $shouldSend = $force ? $now->lt($deadline) : $inWindow;
            if ($shouldSend) {
                $this->notificationService->sendPredictionDeadlineReminderToNonPredictors($race, 'qualifying');
                $this->line("Sent race deadline reminders for {$race->display_name}");
                $sent++;
            }

            if ($race->hasSprint() && $race->sprint_qualifying_start !== null) {
                $sprintDeadline = $race->getSprintPredictionDeadline();
                if ($sprintDeadline !== null) {
                    $sprintInWindow = $now->between(
                        $sprintDeadline->copy()->subHours(72),
                        $sprintDeadline->copy()->addHours(24)
                    );
                    $shouldSendSprint = $force ? $now->lt($sprintDeadline) : $sprintInWindow;
                    if ($shouldSendSprint) {
                        $this->notificationService->sendSprintDeadlineReminderToNonPredictors($race);
                        $this->line("Sent sprint deadline reminders for {$race->display_name}");
                        $sent++;
                    }
                }
            }
        }
        #endregion

        if ($sent === 0) {
            $this->info('No reminder windows active.');
        } else {
            $this->info("Done. Sent {$sent} reminder batch(es).");
        }

        return Command::SUCCESS;
    }
}
