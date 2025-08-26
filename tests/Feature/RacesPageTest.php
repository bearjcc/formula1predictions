<?php

use App\Services\F1ApiService;
use function Pest\Laravel\mock;

test('races page loads successfully', function () {
    // Mock the F1ApiService to return test data
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andReturn([
                [
                    'raceName' => 'Test Race 1',
                    'circuit' => ['circuitName' => 'Test Circuit', 'country' => 'Test Country'],
                    'date' => '2024-03-15',
                    'time' => '14:00:00Z',
                    'status' => 'upcoming',
                    'results' => []
                ]
            ]);
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    $response->assertSee('2024 Races');
    $response->assertSeeLivewire('races.races-list');
});

test('races page shows error state when API fails', function () {
    // Mock the F1ApiService to throw an exception, which will show the error state
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andThrow(new \Exception('API Error'));
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    // The error state should be visible when API fails
    $response->assertSee('Error Loading Races');
    $response->assertSee('Failed to load races: API Error');
});
