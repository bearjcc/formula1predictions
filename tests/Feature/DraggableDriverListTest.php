<?php

use App\Livewire\Predictions\DraggableDriverList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('draggable driver list component can be rendered', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $drivers = [
        [
            'id' => 1,
            'name' => 'Max',
            'surname' => 'Verstappen',
            'nationality' => 'Dutch',
            'team' => ['team_name' => 'Red Bull Racing'],
        ],
        [
            'id' => 2,
            'name' => 'Lewis',
            'surname' => 'Hamilton',
            'nationality' => 'British',
            'team' => ['team_name' => 'Mercedes'],
        ],
    ];

    Livewire::test(DraggableDriverList::class, [
        'drivers' => $drivers,
        'raceName' => 'Monaco GP',
        'season' => 2024,
        'raceRound' => 8,
    ])
        ->assertSee('Your prediction')
        ->assertSee('Drivers (drag into list)');
});

test('draggable driver list initializes with correct driver order', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $drivers = [
        ['id' => 1, 'name' => 'Max', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'team' => ['team_name' => 'Red Bull']],
        ['id' => 2, 'name' => 'Lewis', 'surname' => 'Hamilton', 'nationality' => 'British', 'team' => ['team_name' => 'Mercedes']],
        ['id' => 3, 'name' => 'Charles', 'surname' => 'Leclerc', 'nationality' => 'Monégasque', 'team' => ['team_name' => 'Ferrari']],
    ];

    // Race/sprint (raceRound > 0): pass driverOrder explicitly; component does not auto-fill
    $component = Livewire::test(DraggableDriverList::class, [
        'drivers' => $drivers,
        'raceName' => 'Test Race',
        'season' => 2024,
        'raceRound' => 1,
        'driverOrder' => [1, 2, 3],
    ]);

    expect($component->get('driverOrder'))->toBe([1, 2, 3]);
});

test('draggable driver list race mode keeps empty driver order when not passed', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $drivers = [
        ['id' => 1, 'name' => 'Max', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'team' => ['team_name' => 'Red Bull']],
        ['id' => 2, 'name' => 'Lewis', 'surname' => 'Hamilton', 'nationality' => 'British', 'team' => ['team_name' => 'Mercedes']],
    ];

    $component = Livewire::test(DraggableDriverList::class, [
        'drivers' => $drivers,
        'raceName' => 'Test Race',
        'season' => 2024,
        'raceRound' => 1,
    ]);

    expect($component->get('driverOrder'))->toBe([]);
});

test('draggable driver list championship mode auto-fills driver order when empty', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $drivers = [
        ['id' => 1, 'name' => 'Max', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'team' => ['team_name' => 'Red Bull']],
        ['id' => 2, 'name' => 'Lewis', 'surname' => 'Hamilton', 'nationality' => 'British', 'team' => ['team_name' => 'Mercedes']],
    ];

    $component = Livewire::test(DraggableDriverList::class, [
        'drivers' => $drivers,
        'raceName' => 'Driver Championship',
        'season' => 2024,
        'raceRound' => 0,
    ]);

    expect($component->get('driverOrder'))->toBe([1, 2]);
});

test('draggable driver list can update driver order', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $drivers = [
        ['id' => 1, 'name' => 'Max', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'team' => ['team_name' => 'Red Bull']],
        ['id' => 2, 'name' => 'Lewis', 'surname' => 'Hamilton', 'nationality' => 'British', 'team' => ['team_name' => 'Mercedes']],
        ['id' => 3, 'name' => 'Charles', 'surname' => 'Leclerc', 'nationality' => 'Monégasque', 'team' => ['team_name' => 'Ferrari']],
    ];

    $component = Livewire::test(DraggableDriverList::class, [
        'drivers' => $drivers,
        'raceName' => 'Test Race',
        'season' => 2024,
        'raceRound' => 1,
    ]);

    // Simulate reordering (move driver 3 to position 1)
    $newOrder = [3, 1, 2];
    $component->call('updateDriverOrder', $newOrder);

    expect($component->get('driverOrder'))->toBe($newOrder);
});

test('draggable driver list can set fastest lap', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $drivers = [
        ['id' => 1, 'name' => 'Max', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'team' => ['team_name' => 'Red Bull']],
        ['id' => 2, 'name' => 'Lewis', 'surname' => 'Hamilton', 'nationality' => 'British', 'team' => ['team_name' => 'Mercedes']],
    ];

    $component = Livewire::test(DraggableDriverList::class, [
        'drivers' => $drivers,
        'raceName' => 'Test Race',
        'season' => 2024,
        'raceRound' => 1,
    ]);

    // Set fastest lap (component uses string driverId; Livewire may hydrate as string)
    $component->call('setFastestLap', '1');
    expect($component->get('fastestLapDriverId'))->toBe('1');

    // Change fastest lap
    $component->call('setFastestLap', '2');
    expect($component->get('fastestLapDriverId'))->toBe('2');

    // Unset fastest lap (pass null)
    $component->call('setFastestLap', null);
    expect($component->get('fastestLapDriverId'))->toBeNull();
});

test('draggable driver list exposes driver order and fastest lap state', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $drivers = [
        ['id' => 1, 'name' => 'Max', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'team' => ['team_name' => 'Red Bull']],
        ['id' => 2, 'name' => 'Lewis', 'surname' => 'Hamilton', 'nationality' => 'British', 'team' => ['team_name' => 'Mercedes']],
    ];

    $component = Livewire::test(DraggableDriverList::class, [
        'drivers' => $drivers,
        'raceName' => 'Test Race',
        'season' => 2024,
        'raceRound' => 1,
    ]);

    $component->set('driverOrder', [2, 1]);
    $component->set('fastestLapDriverId', '1');

    expect($component->get('driverOrder'))->toBe([2, 1]);
    expect($component->get('fastestLapDriverId'))->toBe('1');
});

test('draggable driver list handles empty drivers gracefully', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    $component = Livewire::test(DraggableDriverList::class, [
        'drivers' => [],
        'raceName' => 'Test Race',
        'season' => 2024,
        'raceRound' => 1,
    ]);

    expect($component->get('driverOrder'))->toBe([]);
    expect($component->get('fastestLapDriverId'))->toBeNull();
});

// /draggable-demo route smoke test lives in RoutesTest.
