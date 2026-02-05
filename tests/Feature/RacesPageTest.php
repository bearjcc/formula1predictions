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
