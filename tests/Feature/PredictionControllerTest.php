<?php

declare(strict_types=1);

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function testUser(): User
{
    $user = User::factory()->create();
    assert($user instanceof User);

    return $user;
}

test('user can view own prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
    ]);

    $response = actingAs($user)->get(route('predictions.show', $prediction));

    $response->assertOk();
    $response->assertViewIs('predictions.show');
    $response->assertSee($prediction->type);
});

test('scored prediction page shows the score breakdown without the redundant top order card', function () {
    $user = testUser();
    $race = Races::factory()->create([
        'status' => 'completed',
        'results' => [
            [
                'driver' => 'George Russell',
                'driver_id' => 'russell',
                'status' => 'FINISHED',
                'fastest_lap' => false,
            ],
            [
                'driver' => 'Max Verstappen',
                'driver_id' => 'max_verstappen',
                'status' => 'DNF',
                'fastest_lap' => true,
            ],
        ],
    ]);

    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'status' => 'submitted',
        'prediction_data' => [
            'driver_order' => ['russell', 'max_verstappen'],
            'fastest_lap' => 'max_verstappen',
            'dnf_predictions' => ['max_verstappen'],
        ],
    ]);

    $scoring = app(\App\Services\ScoringService::class);
    $score = $scoring->calculatePredictionScore($prediction, $race);
    $scoring->savePredictionScore($prediction, $score);
    $prediction->refresh();

    actingAs($user)
        ->get(route('predictions.show', $prediction))
        ->assertOk()
        ->assertSee('Score Breakdown')
        ->assertSee('Fastest lap')
        ->assertSee('DNF wager')
        ->assertDontSee('Predicted Finishing Order');
});

test('user cannot view another users prediction', function () {
    $owner = testUser();
    $other = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $owner->id,
        'type' => 'race',
    ]);

    actingAs($other)
        ->get(route('predictions.show', $prediction))
        ->assertForbidden();
});

test('guest cannot view prediction', function () {
    $prediction = Prediction::factory()->create();

    get(route('predictions.show', $prediction))
        ->assertRedirect('/login');
});

test('user can update own editable prediction', function () {
    $user = testUser();
    // midseason predictions are always editable (no deadline check), regardless of status
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'midseason',
        'season' => 2024,
        'status' => 'submitted',
        'notes' => 'Original notes',
    ]);

    $response = actingAs($user)
        ->patch(route('predictions.update', $prediction), [
            'notes' => 'Updated notes',
        ]);

    $response->assertRedirect(route('predictions.show', $prediction));
    $response->assertSessionHas('success');

    $prediction->refresh();
    expect($prediction->notes)->toBe('Updated notes');
});

test('user cannot update another users prediction', function () {
    $owner = testUser();
    $other = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $owner->id,
        'type' => 'preseason',
        'status' => 'draft',
    ]);

    actingAs($other)
        ->patch(route('predictions.update', $prediction), ['notes' => 'Hacked'])
        ->assertForbidden();
});

test('user can delete own draft prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'status' => 'draft',
    ]);
    $id = $prediction->id;

    $response = actingAs($user)
        ->delete(route('predictions.destroy', $prediction));

    $response->assertRedirect(route('predictions.index'));
    $response->assertSessionHas('success');
    expect(Prediction::find($id))->toBeNull();
});

test('user cannot delete another users prediction', function () {
    $owner = testUser();
    $other = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $owner->id,
        'type' => 'race',
    ]);

    actingAs($other)
        ->delete(route('predictions.destroy', $prediction))
        ->assertForbidden();

    expect(Prediction::find($prediction->id))->not->toBeNull();
});

test('user cannot update a locked prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
        'status' => 'locked',
        'locked_at' => now(),
        'notes' => 'Original notes',
    ]);

    actingAs($user)
        ->patch(route('predictions.update', $prediction), ['notes' => 'Attempted change'])
        ->assertForbidden();

    $prediction->refresh();
    expect($prediction->notes)->toBe('Original notes');
});

test('user cannot update a scored prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
        'status' => 'scored',
        'locked_at' => now(),
        'scored_at' => now(),
        'notes' => 'Original notes',
    ]);

    actingAs($user)
        ->patch(route('predictions.update', $prediction), ['notes' => 'Attempted change'])
        ->assertForbidden();

    $prediction->refresh();
    expect($prediction->notes)->toBe('Original notes');
});

test('user cannot delete a locked prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'status' => 'locked',
        'locked_at' => now(),
    ]);
    $id = $prediction->id;

    actingAs($user)
        ->delete(route('predictions.destroy', $prediction))
        ->assertForbidden();

    expect(Prediction::find($id))->not->toBeNull();
});

test('user cannot delete a scored prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'status' => 'scored',
        'locked_at' => now(),
        'scored_at' => now(),
    ]);
    $id = $prediction->id;

    actingAs($user)
        ->delete(route('predictions.destroy', $prediction))
        ->assertForbidden();

    expect(Prediction::find($id))->not->toBeNull();
});

test('user can edit a submitted prediction before deadline', function () {
    $user = testUser();
    // midseason has no deadline, so isEditable() always returns true for non-locked/scored
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'midseason',
        'season' => 2024,
        'status' => 'submitted',
        'notes' => 'Original notes',
    ]);

    $response = actingAs($user)
        ->get(route('predictions.edit', $prediction));

    $response->assertOk();
});

test('predict create redirects to edit when prediction already exists for race', function () {
    $user = testUser();
    $race = Races::factory()->create(['season' => 2025, 'round' => 1, 'status' => 'upcoming']);
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2025,
        'race_round' => 1,
        'race_id' => $race->id,
        'status' => 'submitted',
    ]);

    $response = actingAs($user)
        ->get(route('predict.create', ['race_id' => $race->id]));

    $response->assertRedirect(route('predictions.edit', $prediction));
});

test('predict create keeps race and sprint predictions separate for the same round', function () {
    $user = testUser();
    $race = Races::factory()->create([
        'season' => 2025,
        'round' => 2,
        'status' => 'upcoming',
        'has_sprint' => true,
        'qualifying_start' => Carbon::now()->addDays(2),
        'sprint_qualifying_start' => Carbon::now()->addDay(),
    ]);
    $sprintPrediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'sprint',
        'season' => 2025,
        'race_round' => 2,
        'race_id' => $race->id,
        'status' => 'submitted',
    ]);

    actingAs($user)
        ->get(route('predict.create', ['race_id' => $race->id, 'type' => 'race']))
        ->assertOk();

    actingAs($user)
        ->get(route('predict.create', ['race_id' => $race->id, 'type' => 'sprint']))
        ->assertRedirect(route('predictions.edit', $sprintPrediction));
});

test('user can view own locked prediction in read-only edit screen', function () {
    $user = testUser();
    $prediction = Prediction::factory()->locked()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2025,
    ]);

    actingAs($user)
        ->get(route('predictions.edit', $prediction))
        ->assertOk()
        ->assertSee('This prediction is locked.');
});

test('predict create redirects to index with error when race deadline has passed', function () {
    $user = testUser();
    $qualifyingStart = Carbon::now()->addMinutes(20);
    $race = Races::factory()->create([
        'season' => 2025,
        'round' => 1,
        'status' => 'upcoming',
        'qualifying_start' => $qualifyingStart,
        'has_sprint' => false,
    ]);

    $response = actingAs($user)
        ->get(route('predict.create', ['race_id' => $race->id]));

    $response->assertRedirect(route('predictions.index'));
    $response->assertSessionHas('error', 'The prediction deadline for this race has passed.');
});

test('preseason form redirects to index with error when preseason deadline has passed', function () {
    $user = testUser();
    $deadline = Carbon::now()->subHour();
    Races::factory()->create([
        'season' => 2025,
        'round' => 1,
        'status' => 'upcoming',
        'qualifying_start' => $deadline->copy()->addHour(),
    ]);
    Carbon::setTestNow($deadline->copy()->addMinute());

    try {
        $response = actingAs($user)
            ->get(route('predict.preseason', ['year' => 2025]));

        $response->assertRedirect(route('predictions.index'));
        $response->assertSessionHas('error', 'The prediction deadline for preseason has passed.');
    } finally {
        Carbon::setTestNow();
    }
});

test('mass-assigning score or status via create is rejected', function () {
    $user = testUser();
    $prediction = Prediction::create([
        'user_id' => $user->id,
        'type' => 'preseason',
        'season' => 2024,
        'race_round' => 1,
        'race_id' => null,
        'prediction_data' => ['team_order' => [1], 'driver_championship' => [1]],
        'notes' => null,
        'score' => 500,
        'accuracy' => 99.9,
        'status' => 'scored',
        'submitted_at' => now(),
        'locked_at' => now(),
        'scored_at' => now(),
    ]);

    $prediction->refresh();
    expect($prediction->score)->toBe(0)
        ->and($prediction->status)->toBe('draft')
        ->and($prediction->accuracy)->toBeNull()
        ->and($prediction->submitted_at)->toBeNull()
        ->and($prediction->locked_at)->toBeNull()
        ->and($prediction->scored_at)->toBeNull();
});
