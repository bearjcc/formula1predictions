<?php

use App\Livewire\Races\RacesList;
use App\Services\F1ApiService;
use Livewire\Livewire;

use function Pest\Laravel\mock;

test('races page loads successfully', function () {
    // Mock the F1ApiService to return test data
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andReturn([
                [
                    'round' => 1,
                    'raceName' => 'Test Race 1',
                    'circuit' => ['circuitName' => 'Test Circuit', 'country' => 'Test Country'],
                    'date' => '2024-03-15',
                    'time' => '14:00:00Z',
                    'status' => 'upcoming',
                    'results' => [],
                ],
            ]);
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    $response->assertSee('2024 Races');
    $response->assertSeeLivewire('races.races-list');
});

test('races page shows error state when API fails', function () {
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andThrow(new \App\Exceptions\F1ApiException('API Error', 500, '/2024/1/race', 2024));
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    $response->assertSee('Error Loading Races');
    $response->assertSee('We\'re having trouble loading race data right now');
});

test('races page shows user-friendly error for connection failures', function () {
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andThrow(new \Exception('Connection refused'));
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    $response->assertSee('Error Loading Races');
    $response->assertSee('We\'re having trouble loading race data right now');
});

test('races page does not expose technical error details to user', function () {
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andThrow(new \Exception('cURL error 28: Operation timed out'));
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    $response->assertDontSee('cURL error');
    $response->assertDontSee('Operation timed out');
});

test('current season races page returns 200 and shows error state when API fails', function () {
    $year = (int) config('f1.current_season', 2026);

    mock(F1ApiService::class, function ($mock) use ($year) {
        $mock->shouldReceive('getRacesForYear')
            ->with($year)
            ->andThrow(new \App\Exceptions\F1ApiException('Service unavailable', 503, "/{$year}/1/race", $year));
    });

    $response = $this->get("/{$year}/races");

    $response->assertOk();
    $response->assertSee((string) $year);
    $response->assertSee('Error Loading Races');
    $response->assertSee('We\'re having trouble loading race data right now');
});

test('races list shows all races grouped as next, future, and past', function () {
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andReturn([
                [
                    'round' => 1,
                    'raceName' => 'Australian Grand Prix',
                    'circuit' => [
                        'circuitName' => 'Albert Park',
                        'country' => 'Australia',
                    ],
                    'date' => '2024-03-15',
                    'time' => '04:00:00Z',
                    'status' => 'upcoming',
                    'results' => [],
                ],
                [
                    'round' => 2,
                    'raceName' => 'Canadian Grand Prix',
                    'circuit' => [
                        'circuitName' => 'Circuit Gilles Villeneuve',
                        'country' => 'Canada',
                    ],
                    'date' => '2024-06-09',
                    'time' => '18:00:00Z',
                    'status' => 'completed',
                    'results' => [
                        ['driver' => 'Sample Driver'],
                    ],
                ],
            ]);
    });

    Livewire::test(RacesList::class, ['year' => 2024])
        ->assertSee('Australian Grand Prix')
        ->assertSee('Canadian Grand Prix')
        ->assertSee('Next race')
        ->assertSee('Past races');
});
