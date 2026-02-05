<?php

declare(strict_types=1);

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('leaderboard index renders for authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('leaderboard.index'));

    $response->assertSuccessful();
    $response->assertSee('Leaderboard');
    $response->assertSee('Head-to-Head Compare');
});

test('leaderboard index unchanged when no predictions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('leaderboard.index'));

    $response->assertSuccessful();
    $response->assertSee('No predictions found');
});

test('leaderboard compare page renders with empty selection', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('leaderboard.compare'));

    $response->assertSuccessful();
    $response->assertSee('Head-to-Head Comparison');
    $response->assertSee('Select one or more predictors');
});

test('leaderboard compare with users shows comparison data and shareable URL', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);
    $this->actingAs($user1);

    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'results' => [['driver_id' => 1, 'position' => 1]],
    ]);

    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race->id,
        'season' => 2024,
        'type' => 'race',
        'status' => 'scored',
        'score' => 20,
        'accuracy' => 80,
    ]);
    Prediction::factory()->create([
        'user_id' => $user2->id,
        'race_id' => $race->id,
        'season' => 2024,
        'type' => 'race',
        'status' => 'scored',
        'score' => 15,
        'accuracy' => 60,
    ]);

    $response = $this->get(route('leaderboard.compare', [
        'season' => 2024,
        'users' => implode(',', [$user1->id, $user2->id]),
    ]));

    $response->assertSuccessful();
    $response->assertSee('Alice');
    $response->assertSee('Bob');
    $response->assertSee('20');
    $response->assertSee('15');
    $response->assertSee('Share this comparison');
    $response->assertSee('Cumulative Score Progression');
});

test('leaderboard compare supports users array from form', function () {
    $user1 = User::factory()->create(['name' => 'Charlie']);
    $this->actingAs($user1);

    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'results' => [['driver_id' => 1, 'position' => 1]],
    ]);
    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race->id,
        'season' => 2024,
        'type' => 'race',
        'status' => 'scored',
        'score' => 25,
        'accuracy' => 100,
    ]);

    $response = $this->get(route('leaderboard.compare', [
        'season' => 2024,
        'users' => [$user1->id],
    ]));

    $response->assertSuccessful();
    $response->assertSee('Charlie');
    $response->assertSee('25');
});

test('leaderboard compare filters out invalid user ids', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('leaderboard.compare', [
        'season' => 2024,
        'users' => '99999,88888',
    ]));

    $response->assertSuccessful();
    $response->assertSee('Select one or more predictors');
});

test('leaderboard user-stats has compare link', function () {
    $viewer = User::factory()->create();
    $subject = User::factory()->create(['name' => 'Test User']);
    $this->actingAs($viewer);

    $race = Races::factory()->create(['season' => 2024, 'results' => []]);
    Prediction::factory()->create([
        'user_id' => $subject->id,
        'race_id' => $race->id,
        'season' => 2024,
        'status' => 'scored',
        'score' => 10,
    ]);

    $response = $this->get(route('leaderboard.user-stats', $subject));

    $response->assertSuccessful();
    $response->assertSee('Head-to-Head Compare');
});
