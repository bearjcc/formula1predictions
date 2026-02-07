<?php

use App\Livewire\Predictions\DraggableDriverList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('draggable driver list component can be rendered', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

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
        ->assertSee('Monaco GP Predicted Order')
        ->assertSee('Drag to reorder');
});

test('draggable driver list initializes with correct driver order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

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

    // Check that driver order is initialized correctly
    expect($component->get('driverOrder'))->toBe([1, 2, 3]);
});

test('draggable driver list can update driver order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

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
    $user = User::factory()->create();
    $this->actingAs($user);

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

    // Unset fastest lap (click same driver again)
    $component->call('setFastestLap', '2');
    expect($component->get('fastestLapDriverId'))->toBeNull();
});

test('draggable driver list exposes driver order and fastest lap state', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

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
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(DraggableDriverList::class, [
        'drivers' => [],
        'raceName' => 'Test Race',
        'season' => 2024,
        'raceRound' => 1,
    ]);

    expect($component->get('driverOrder'))->toBe([]);
    expect($component->get('fastestLapDriverId'))->toBeNull();
});

test('draggable driver list demo page loads correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/draggable-demo');

    $response->assertStatus(200);
    $response->assertSee('Draggable Driver Predictions Demo');
    $response->assertSee('Monaco Grand Prix Predicted Order');
    $response->assertSee('Drag to reorder');
});
