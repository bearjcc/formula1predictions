<?php

declare(strict_types=1);

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\HasAdminAndUser;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class, HasAdminAndUser::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'admin@example.com', 'is_admin' => true]);
    $this->user = User::factory()->create(['email' => 'user@example.com']);
});

test('admin can load all admin view pages', function () {
    actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
    actingAs($this->admin)
        ->get(route('admin.users'))
        ->assertOk();
    actingAs($this->admin)
        ->get(route('admin.predictions'))
        ->assertOk();
    actingAs($this->admin)
        ->get(route('admin.races'))
        ->assertOk();
    actingAs($this->admin)
        ->get(route('admin.scoring'))
        ->assertOk();
    actingAs($this->admin)
        ->get(route('admin.settings'))
        ->assertOk();
});

test('regular user cannot score prediction', function () {
    $race = Races::factory()->create(['season' => 2024, 'round' => 1]);
    $prediction = Prediction::factory()->submitted()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    actingAs($this->user)
        ->post(route('admin.predictions.score', $prediction))
        ->assertForbidden();
});

test('admin can score prediction', function () {
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'status' => 'completed',
        'results' => [
            ['driver' => ['driverId' => 'max_verstappen'], 'status' => 'finished'],
            ['driver' => ['driverId' => 'lewis_hamilton'], 'status' => 'finished'],
        ],
    ]);
    $prediction = Prediction::factory()->submitted()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'prediction_data' => [
            'driver_order' => ['max_verstappen', 'lewis_hamilton'],
            'fastest_lap' => 'max_verstappen',
        ],
    ]);

    actingAs($this->admin)
        ->from(route('admin.predictions'))
        ->post(route('admin.predictions.score', $prediction))
        ->assertRedirect(route('admin.predictions'))
        ->assertSessionHas('success');

    $prediction->refresh();
    expect($prediction->status)->toBe('scored')
        ->and($prediction->score)->not->toBeNull();
});

test('admin cannot score non-race prediction', function () {
    $prediction = Prediction::factory()->submitted()->create([
        'user_id' => $this->user->id,
        'race_id' => null,
        'type' => 'preseason',
    ]);

    actingAs($this->admin)
        ->from(route('admin.predictions'))
        ->post(route('admin.predictions.score', $prediction))
        ->assertRedirect(route('admin.predictions'))
        ->assertSessionHas('error');
});

test('regular user cannot lock prediction', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->submitted()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    actingAs($this->user)
        ->post(route('admin.predictions.lock', $prediction))
        ->assertForbidden();
});

test('admin can lock prediction', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->submitted()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    actingAs($this->admin)
        ->from(route('admin.predictions'))
        ->post(route('admin.predictions.lock', $prediction))
        ->assertRedirect(route('admin.predictions'))
        ->assertSessionHas('success');

    $prediction->refresh();
    expect($prediction->status)->toBe('locked')
        ->and($prediction->locked_at)->not->toBeNull();
});

test('admin can unlock prediction', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->locked()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    actingAs($this->admin)
        ->from(route('admin.predictions'))
        ->post(route('admin.predictions.unlock', $prediction))
        ->assertRedirect(route('admin.predictions'))
        ->assertSessionHas('success');

    $prediction->refresh();
    expect($prediction->status)->toBe('draft')
        ->and($prediction->locked_at)->toBeNull();
});

test('regular user cannot delete prediction via admin', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'status' => 'draft',
    ]);

    actingAs($this->user)
        ->delete(route('admin.predictions.delete', $prediction))
        ->assertForbidden();
});

test('admin can delete prediction', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);
    $id = $prediction->id;

    actingAs($this->admin)
        ->from(route('admin.predictions'))
        ->delete(route('admin.predictions.delete', $prediction))
        ->assertRedirect(route('admin.predictions'))
        ->assertSessionHas('success');

    expect(Prediction::find($id))->toBeNull();
});

test('regular user cannot score race', function () {
    $race = Races::factory()->create();

    actingAs($this->user)
        ->post(route('admin.races.score', $race))
        ->assertForbidden();
});

test('admin can score race', function () {
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'status' => 'completed',
        'results' => [
            ['driver' => ['driverId' => 'max_verstappen'], 'status' => 'finished'],
        ],
    ]);
    Prediction::factory()->submitted()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'prediction_data' => [
            'driver_order' => ['max_verstappen'],
            'fastest_lap' => 'max_verstappen',
        ],
    ]);

    actingAs($this->admin)
        ->from(route('admin.scoring'))
        ->post(route('admin.races.score', $race))
        ->assertRedirect(route('admin.scoring'))
        ->assertSessionHas('success');
});

test('admin can queue race scoring', function () {
    $race = Races::factory()->create(['race_name' => 'Monaco GP']);

    actingAs($this->admin)
        ->from(route('admin.scoring'))
        ->post(route('admin.races.queue-scoring', $race), ['force_update' => false])
        ->assertRedirect(route('admin.scoring'))
        ->assertSessionHas('success');
});

test('regular user cannot override prediction score', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->scored()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    actingAs($this->user)
        ->post(route('admin.predictions.override-score', $prediction), [
            'score' => 100,
            'reason' => 'Test',
        ])
        ->assertForbidden();
});

test('admin can override prediction score', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->scored(50)->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    actingAs($this->admin)
        ->from(route('admin.predictions'))
        ->post(route('admin.predictions.override-score', $prediction), [
            'score' => 100,
            'reason' => 'Admin adjustment',
        ])
        ->assertRedirect(route('admin.predictions'))
        ->assertSessionHas('success');

    $prediction->refresh();
    expect($prediction->score)->toBe(100);
});

test('override score validation rejects invalid score', function () {
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->scored()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    actingAs($this->admin)
        ->from(route('admin.predictions'))
        ->post(route('admin.predictions.override-score', $prediction), [
            'score' => 999,
        ])
        ->assertSessionHasErrors('score');
});

test('regular user cannot handle driver substitutions', function () {
    $race = Races::factory()->create();

    actingAs($this->user)
        ->post(route('admin.races.substitutions', $race), [
            'substitutions' => [
                ['old_driver_id' => '1', 'new_driver_id' => '2'],
            ],
        ])
        ->assertForbidden();
});

test('admin can handle driver substitutions', function () {
    $race = Races::factory()->create(['results' => [['driver_id' => 1, 'position' => 1]]]);

    actingAs($this->admin)
        ->from(route('admin.scoring'))
        ->post(route('admin.races.substitutions', $race), [
            'substitutions' => [
                ['old_driver_id' => '1', 'new_driver_id' => '2'],
            ],
        ])
        ->assertRedirect(route('admin.scoring'))
        ->assertSessionHas('success');
});

test('substitutions validation requires substitutions array', function () {
    $race = Races::factory()->create();

    actingAs($this->admin)
        ->from(route('admin.scoring'))
        ->post(route('admin.races.substitutions', $race), [])
        ->assertSessionHasErrors('substitutions');
});

test('regular user cannot cancel race', function () {
    $race = Races::factory()->create();

    actingAs($this->user)
        ->post(route('admin.races.cancel', $race), ['reason' => 'Weather'])
        ->assertForbidden();
});

test('admin can cancel race', function () {
    $race = Races::factory()->create();

    actingAs($this->admin)
        ->from(route('admin.scoring'))
        ->post(route('admin.races.cancel', $race), ['reason' => 'Weather'])
        ->assertRedirect(route('admin.scoring'))
        ->assertSessionHas('success');
});

test('regular user cannot toggle half-points', function () {
    $race = Races::factory()->create(['half_points' => false]);

    actingAs($this->user)
        ->post(route('admin.races.toggle-half-points', $race))
        ->assertForbidden();
});

test('admin can toggle half-points', function () {
    $race = Races::factory()->create(['half_points' => false]);

    actingAs($this->admin)
        ->from(route('admin.scoring'))
        ->post(route('admin.races.toggle-half-points', $race))
        ->assertRedirect(route('admin.scoring'))
        ->assertSessionHas('success');

    $race->refresh();
    expect($race->half_points)->toBeTrue();

    actingAs($this->admin)
        ->from(route('admin.scoring'))
        ->post(route('admin.races.toggle-half-points', $race))
        ->assertRedirect(route('admin.scoring'))
        ->assertSessionHas('success');

    $race->refresh();
    expect($race->half_points)->toBeFalse();
});

test('regular user cannot get race scoring stats', function () {
    $race = Races::factory()->create();

    actingAs($this->user)
        ->getJson(route('admin.races.scoring-stats', $race))
        ->assertForbidden();
});

test('admin can get race scoring stats', function () {
    $race = Races::factory()->create();

    $response = actingAs($this->admin)
        ->getJson(route('admin.races.scoring-stats', $race));

    $response->assertOk();
    $response->assertJsonStructure(['total_predictions', 'average_score', 'highest_score', 'lowest_score', 'perfect_predictions']);
});

test('regular user cannot bulk score races', function () {
    $race = Races::factory()->create();

    actingAs($this->user)
        ->post(route('admin.bulk-score'), ['race_ids' => [$race->id]])
        ->assertForbidden();
});

test('admin can bulk score races', function () {
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'status' => 'completed',
        'results' => [
            ['driver' => ['driverId' => 'max_verstappen'], 'status' => 'finished'],
        ],
    ]);
    Prediction::factory()->submitted()->create([
        'user_id' => $this->user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'prediction_data' => [
            'driver_order' => ['max_verstappen'],
            'fastest_lap' => 'max_verstappen',
        ],
    ]);

    actingAs($this->admin)
        ->from(route('admin.scoring'))
        ->post(route('admin.bulk-score'), ['race_ids' => [$race->id]])
        ->assertRedirect(route('admin.scoring'))
        ->assertSessionHas('success');
});

test('bulk score validation requires race_ids', function () {
    actingAs($this->admin)
        ->post(route('admin.bulk-score'), [])
        ->assertSessionHasErrors('race_ids');
});
