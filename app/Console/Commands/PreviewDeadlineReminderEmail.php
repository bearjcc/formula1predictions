<?php

namespace App\Console\Commands;

use App\Mail\PredictionDeadlineReminderMail;
use App\Models\Races;
use App\Notifications\PredictionDeadlineReminderPreview;
use App\Notifications\PreviewRecipient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;

class PreviewDeadlineReminderEmail extends Command
{
    protected $signature = 'reminders:preview
                            {--to= : Send preview to this email (default: ADMIN_EMAIL from config)}
                            {--type=qualifying : Reminder type: qualifying, sprint, preseason, midseason}
                            {--export= : Write HTML to this path for local preview (e.g. storage/app/deadline-preview.html)}';

    protected $description = 'Preview the prediction deadline reminder email: send to an inbox and/or export as HTML.';

    public function handle(): int
    {
        $to = $this->option('to') ?: config('admin.promotable_admin_email');
        $type = $this->option('type');
        $exportPath = $this->option('export');

        $validTypes = ['qualifying', 'sprint', 'preseason', 'midseason'];
        if (! in_array($type, $validTypes, true)) {
            $this->error("Invalid --type. Use one of: " . implode(', ', $validTypes));

            return Command::FAILURE;
        }

        $race = $this->buildSampleRace($type);
        $notifiable = new PreviewRecipient('Preview Recipient', $to ?: 'preview@example.com');

        $notification = new PredictionDeadlineReminderPreview($race, $type);
        $mailable = $notification->toMail($notifiable);

        if ($exportPath !== null) {
            $this->exportHtml($exportPath, $mailable);
            $this->info("HTML written to: {$exportPath}");
        }

        $wantsSend = $this->option('export') === null || $this->option('to') !== null;
        if ($wantsSend && $to !== null && $to !== '') {
            $this->info("Sending preview to: {$to}");
            Notification::sendNow([$notifiable], $notification);
            $this->info('Sent. Check your inbox (and spam).');
        } elseif ($exportPath === null) {
            $this->warn('No recipient. Set --to=your@email.com or ADMIN_EMAIL in .env to send a preview, or use --export=path to save HTML only.');
        }

        return Command::SUCCESS;
    }

    private function buildSampleRace(string $type): Races
    {
        $season = config('f1.current_season');
        $qualifyingStart = Carbon::now()->addDays(1)->setHour(14)->setMinute(0);

        if ($type === 'preseason') {
            $first = Races::where('season', $season)->orderBy('round')->first();
            if ($first !== null) {
                return $first;
            }

            return new Races([
                'season' => $season,
                'race_name' => "{$season} Season",
                'round' => 0,
                'qualifying_start' => $qualifyingStart,
            ]);
        }

        if ($type === 'midseason') {
            return new Races([
                'season' => $season,
                'race_name' => "{$season} Midseason",
                'round' => 0,
            ]);
        }

        $existing = Races::where('season', $season)->whereNotNull('qualifying_start')->orderBy('round')->first();
        if ($existing !== null) {
            if ($type === 'sprint' && $existing->hasSprint() && $existing->sprint_qualifying_start === null) {
                $existing->setAttribute('sprint_qualifying_start', $qualifyingStart->copy()->subHours(2));
            }

            return $existing;
        }

        $race = new Races([
            'season' => $season,
            'round' => 1,
            'race_name' => 'Sample Grand Prix',
            'qualifying_start' => $qualifyingStart,
            'sprint_qualifying_start' => $type === 'sprint' ? $qualifyingStart->copy()->subHours(2) : null,
            'has_sprint' => $type === 'sprint',
        ]);
        $race->id = 1;

        return $race;
    }

    private function exportHtml(string $path, PredictionDeadlineReminderMail $mailable): void
    {
        $html = View::make('emails.deadline-reminder', [
            'subject' => "Prediction Deadline Reminder: {$mailable->displayName}",
            'recipientName' => $mailable->recipientName,
            'displayName' => $mailable->displayName,
            'deadlineText' => $mailable->deadlineText,
            'deadlineNzt' => $mailable->deadlineNzt,
            'deadlineEst' => $mailable->deadlineEst,
            'actionUrl' => $mailable->actionUrl,
            'actionText' => $mailable->actionText,
        ])->render();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $html);
    }
}
