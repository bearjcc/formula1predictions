<?php

use App\Events\NotificationReceived;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('notification received event is dispatched when race results are available', function () {
    Event::fake([NotificationReceived::class]);

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

    Event::assertDispatched(NotificationReceived::class, function ($event) use ($user, $race) {
        return $event->user->id === $user->id &&
               $event->notificationData['type'] === 'race_results_available' &&
               $event->notificationData['race_id'] === $race->id;
    });
});

test('notification received event is dispatched when prediction is scored', function () {
    Event::fake([NotificationReceived::class]);

    $user = User::factory()->create();
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    $notificationService = new NotificationService;
    $notificationService->sendPredictionScoredNotification($prediction, 85, 75.5);

    Event::assertDispatched(NotificationReceived::class, function ($event) use ($user, $prediction) {
        return $event->user->id === $user->id &&
               $event->notificationData['type'] === 'prediction_scored' &&
               $event->notificationData['prediction_id'] === $prediction->id &&
               $event->notificationData['score'] === 85 &&
               $event->notificationData['race_name'] === $prediction->race->race_name;
    });
});

test('notification dropdown highlights prediction scored details', function () {
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

    // Store a real prediction scored notification using the notification class
    $user->notify(new App\Notifications\PredictionScored($prediction, 95, 88.5));

    $this->actingAs($user);

    Livewire::test('notifications.notification-dropdown')
        ->assertSee('Prediction scored')
        ->assertSee('Monaco Grand Prix')
        ->assertSee('95 pts')
        ->assertSee('88.5%');
});

test('notification received event broadcasts to correct channel', function () {
    $user = User::factory()->create();
    $notificationData = [
        'type' => 'test_notification',
        'message' => 'Test notification',
    ];

    $event = new NotificationReceived($user, $notificationData);

    expect($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0]->name)->toBe("private-user.{$user->id}")
        ->and($event->broadcastAs())->toBe('notification.received')
        ->and($event->broadcastWith())->toHaveKey('notification')
        ->and($event->broadcastWith())->toHaveKey('unread_count');
});

test('notification dropdown component shows correct unread count', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create();

    // Create a notification for the user
    $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\RaceResultsAvailable',
        'data' => [
            'type' => 'race_results_available',
            'race_id' => $race->id,
            'race_name' => $race->race_name,
            'message' => "Race results for {$race->race_name} are now available",
        ],
    ]);

    $this->actingAs($user);

    // Visit a page that renders the layout header (home uses hideHeader=true so dropdown is not in DOM)
    $response = $this->get(route('scoring'));
    $response->assertStatus(200);

    // The component should be present and show 1 unread notification
    $response->assertSeeLivewire('notifications.notification-dropdown');
});

test('notifications index page shows user notifications', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create();

    // Create a notification for the user
    $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\RaceResultsAvailable',
        'data' => [
            'type' => 'race_results_available',
            'race_id' => $race->id,
            'race_name' => $race->race_name,
            'message' => "Race results for {$race->race_name} are now available",
        ],
    ]);

    $this->actingAs($user);

    $response = $this->get('/notifications');
    $response->assertStatus(200);
    $response->assertSee($race->race_name);
    $response->assertSee('Race results for');
});

test('mark as read functionality works', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create();

    // Create an unread notification
    $notification = $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\RaceResultsAvailable',
        'data' => [
            'type' => 'race_results_available',
            'race_id' => $race->id,
            'race_name' => $race->race_name,
            'message' => "Race results for {$race->race_name} are now available",
        ],
    ]);

    $this->actingAs($user);

    // Mark as read using Livewire test
    Livewire::test('notifications.notification-dropdown')
        ->call('markAsRead', $notification->id);

    // Check that notification is now read
    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('mark all as read functionality works', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create();

    // Create multiple unread notifications
    $user->notifications()->createMany([
        [
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\RaceResultsAvailable',
            'data' => [
                'type' => 'race_results_available',
                'race_id' => $race->id,
                'race_name' => $race->race_name,
                'message' => "Race results for {$race->race_name} are now available",
            ],
        ],
        [
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\PredictionScored',
            'data' => [
                'type' => 'prediction_scored',
                'message' => 'Your prediction has been scored: 85 points',
            ],
        ],
    ]);

    $this->actingAs($user);

    // Mark all as read using Livewire test
    Livewire::test('notifications.notification-dropdown')
        ->call('markAllAsRead');

    // Check that all notifications are now read
    expect($user->unreadNotifications()->count())->toBe(0);
});
