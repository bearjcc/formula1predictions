<?php

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Notifications\PredictionDeadlineReminder;
use App\Notifications\PredictionScored;
use App\Notifications\RaceResultsAvailable;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('race results available notification can be sent', function () {
    Notification::fake();

    $user = User::factory()->create();
    $race = Races::factory()->create([
        'race_name' => 'Monaco Grand Prix',
        'season' => 2024,
        'round' => 8,
    ]);

    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    $notificationService = new NotificationService;
    $notificationService->sendRaceResultsAvailableNotification($race);

    Notification::assertSentTo($user, RaceResultsAvailable::class, function ($notification) use ($race) {
        return $notification->race->id === $race->id;
    });
});

test('prediction scored notification can be sent', function () {
    Notification::fake();

    $user = User::factory()->create();
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    $notificationService = new NotificationService;
    $notificationService->sendPredictionScoredNotification($prediction, 85, 75.5);

    Notification::assertSentTo($user, PredictionScored::class, function ($notification) use ($prediction) {
        return $notification->prediction->id === $prediction->id &&
               $notification->score === 85 &&
               $notification->accuracy === 75.5;
    });
});

test('prediction deadline reminder can be sent to all users', function () {
    Notification::fake();

    $users = User::factory()->count(3)->create();
    $race = Races::factory()->create([
        'race_name' => 'British Grand Prix',
        'season' => 2024,
        'round' => 12,
    ]);

    $notificationService = new NotificationService;
    $notificationService->sendPredictionDeadlineReminder($race, 'qualifying');

    foreach ($users as $user) {
        Notification::assertSentTo($user, PredictionDeadlineReminder::class, function ($notification) use ($race) {
            return $notification->race->id === $race->id &&
                   $notification->deadlineType === 'qualifying';
        });
    }
});

test('prediction deadline reminder can be sent to non-predictors only', function () {
    Notification::fake();

    $predictor = User::factory()->create();
    $nonPredictor = User::factory()->create();
    $race = Races::factory()->create();

    // Create prediction for one user
    Prediction::factory()->create([
        'user_id' => $predictor->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    $notificationService = new NotificationService;
    $notificationService->sendPredictionDeadlineReminderToNonPredictors($race);

    // Should not send to user who already predicted
    Notification::assertNotSentTo($predictor, PredictionDeadlineReminder::class);

    // Should send to user who hasn't predicted
    Notification::assertSentTo($nonPredictor, PredictionDeadlineReminder::class);
});

test('preseason deadline reminder can be sent', function () {
    Notification::fake();

    $users = User::factory()->count(2)->create();

    $notificationService = new NotificationService;
    $notificationService->sendPreseasonDeadlineReminder(2024);

    foreach ($users as $user) {
        Notification::assertSentTo($user, PredictionDeadlineReminder::class, function ($notification) {
            return $notification->deadlineType === 'preseason';
        });
    }
});

test('midseason deadline reminder can be sent', function () {
    Notification::fake();

    $users = User::factory()->count(2)->create();

    $notificationService = new NotificationService;
    $notificationService->sendMidseasonDeadlineReminder(2024);

    foreach ($users as $user) {
        Notification::assertSentTo($user, PredictionDeadlineReminder::class, function ($notification) {
            return $notification->deadlineType === 'midseason';
        });
    }
});

test('race results available notification has correct email content', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $race = Races::factory()->create([
        'race_name' => 'Monaco Grand Prix',
        'season' => 2024,
        'round' => 8,
    ]);

    $notification = new RaceResultsAvailable($race);
    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Race Results Available: Monaco Grand Prix')
        ->and($mailMessage->greeting)->toBe('Hello John Doe!')
        ->and($mailMessage->introLines)->toContain('The results for Monaco Grand Prix are now available.')
        ->and($mailMessage->introLines)->toContain('Your predictions for this race have been scored and your points have been updated.');
});

test('prediction scored notification has correct email content', function () {
    $user = User::factory()->create(['name' => 'Jane Smith']);
    $race = Races::factory()->create([
        'race_name' => 'Monaco Grand Prix',
        'season' => 2024,
    ]);
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    $notification = new PredictionScored($prediction, 95, 88.5);
    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Prediction Scored: Race Prediction')
        ->and($mailMessage->greeting)->toBe('Hello Jane Smith!')
        ->and($mailMessage->introLines)->toContain('Your Race Prediction has been scored!')
        ->and($mailMessage->introLines)->toContain('Race: Monaco Grand Prix');
});

test('prediction scored notification stores detailed data for dropdown', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create([
        'race_name' => 'Monaco Grand Prix',
        'season' => 2024,
    ]);
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    $notification = new PredictionScored($prediction, 95, 88.5);
    $array = $notification->toArray($user);

    expect($array)
        ->toHaveKey('type', 'prediction_scored')
        ->and($array['prediction_id'])->toBe($prediction->id)
        ->and($array['race_name'])->toBe('Monaco Grand Prix')
        ->and($array['score'])->toBe(95)
        ->and($array['accuracy'])->toBe(88.5)
        ->and($array['message'])->toContain('Monaco Grand Prix')
        ->and($array['message'])->toContain('95 points')
        ->and($array['message'])->toContain('88.5%')
        ->and($array['action_url'])->toBe('/predictions');
});

test('prediction deadline reminder has correct email content', function () {
    $user = User::factory()->create(['name' => 'Bob Wilson']);
    $qualifyingStart = Carbon::now()->addDays(1)->setHour(14)->setMinute(0);
    $race = Races::factory()->create([
        'race_name' => 'British Grand Prix',
        'season' => 2024,
        'round' => 12,
        'qualifying_start' => $qualifyingStart,
    ]);

    $notification = new PredictionDeadlineReminder($race, 'qualifying');
    $mailable = $notification->toMail($user);

    expect($mailable->displayName)->toBe('British Grand Prix')
        ->and($mailable->recipientName)->toBe('Bob Wilson')
        ->and($mailable->deadlineText)->toBe('qualifying session');

    $deadline = $qualifyingStart->copy()->subHour();
    $nzt = $deadline->copy()->timezone('Pacific/Auckland')->format('M j, Y g:i A T');
    $est = $deadline->copy()->timezone('America/New_York')->format('M j, Y g:i A T');
    expect($mailable->deadlineNzt)->toBe($nzt)
        ->and($mailable->deadlineEst)->toBe($est);
});

test('prediction deadline reminder has correct action url for race', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create(['season' => 2026, 'round' => 1]);

    $notification = new PredictionDeadlineReminder($race, 'qualifying');
    $mailable = $notification->toMail($user);
    $array = $notification->toArray($user);

    expect($mailable->actionUrl)->toContain('predict/create')
        ->and($mailable->actionUrl)->toContain('race_id=' . $race->id)
        ->and($array['action_url'])->toBe('/predict/create?race_id=' . $race->id);
});

test('prediction deadline reminder has correct action url for preseason', function () {
    $user = User::factory()->create();
    $race = Races::getFirstRaceOfSeason(2026) ?? new Races(['season' => 2026, 'race_name' => '2026 Season', 'round' => 0]);

    $notification = new PredictionDeadlineReminder($race, 'preseason');
    $mailable = $notification->toMail($user);
    $array = $notification->toArray($user);

    expect($mailable->actionUrl)->toContain('predict/preseason')
        ->and($mailable->actionUrl)->toContain('year=2026')
        ->and($array['action_url'])->toBe('/predict/preseason?year=2026');
});

test('notifications are stored in database', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create();

    $notification = new RaceResultsAvailable($race);
    $user->notify($notification);

    expect($user->notifications)->toHaveCount(1)
        ->and($user->notifications->first()->data['type'])->toBe('race_results_available')
        ->and($user->notifications->first()->data['race_id'])->toBe($race->id);
});

test('notifications implement ShouldQueue interface', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create();

    expect(new RaceResultsAvailable($race))->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class)
        ->and(new PredictionScored($prediction, 100, 90.0))->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class)
        ->and(new PredictionDeadlineReminder($race))->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});

test('race results notification is only sent to users who predicted the race', function () {
    Notification::fake();

    $race = Races::factory()->create();

    $predictor = User::factory()->create();
    $nonPredictor = User::factory()->create();

    Prediction::factory()->create([
        'user_id' => $predictor->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    $notificationService = new NotificationService;
    $notificationService->sendRaceResultsAvailableNotification($race);

    Notification::assertSentTo($predictor, RaceResultsAvailable::class);
    Notification::assertNotSentTo($nonPredictor, RaceResultsAvailable::class);
});

test('preseason deadline reminder can be sent to non-predictors only', function () {
    Notification::fake();

    Races::factory()->create(['season' => 2024, 'round' => 1]);
    $withPreseason = User::factory()->create();
    $withoutPreseason = User::factory()->create();

    Prediction::factory()->create([
        'user_id' => $withPreseason->id,
        'season' => 2024,
        'type' => 'preseason',
        'race_id' => null,
        'race_round' => 0,
        'prediction_data' => ['team_order' => [1, 2, 3]],
    ]);

    $notificationService = new NotificationService;
    $notificationService->sendPreseasonDeadlineReminderToNonPredictors(2024);

    Notification::assertNotSentTo($withPreseason, PredictionDeadlineReminder::class);
    Notification::assertSentTo($withoutPreseason, PredictionDeadlineReminder::class, function ($n) {
        return $n->deadlineType === 'preseason';
    });
});

test('sprint deadline reminder can be sent to non-predictors only', function () {
    Notification::fake();

    $race = Races::factory()->create(['has_sprint' => true]);
    $withSprint = User::factory()->create();
    $withoutSprint = User::factory()->create();

    Prediction::factory()->create([
        'user_id' => $withSprint->id,
        'race_id' => $race->id,
        'type' => 'sprint',
    ]);

    $notificationService = new NotificationService;
    $notificationService->sendSprintDeadlineReminderToNonPredictors($race);

    Notification::assertNotSentTo($withSprint, PredictionDeadlineReminder::class);
    Notification::assertSentTo($withoutSprint, PredictionDeadlineReminder::class, function ($n) use ($race) {
        return $n->deadlineType === 'sprint' && $n->race->id === $race->id;
    });
});

test('deadline reminder is not sent again to users who already received it', function () {
    $race = Races::factory()->create();
    $user = User::factory()->create();

    $user->notify(new PredictionDeadlineReminder($race, 'qualifying'));
    expect($user->notifications()->count())->toBe(1);

    Notification::fake();
    $notificationService = new NotificationService;
    $notificationService->sendPredictionDeadlineReminderToNonPredictors($race, 'qualifying');

    Notification::assertNotSentTo($user, PredictionDeadlineReminder::class);
});
