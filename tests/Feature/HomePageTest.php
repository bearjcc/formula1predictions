<?php

test('home page loads successfully', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('F1 Predictions');
});

test('home page hero start predicting link points to login or predict create', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Start Predicting');
    $response->assertSee('View Standings');
});

test('home page card links resolve to valid routes', function () {
    $year = config('f1.current_season');

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee(route('races', ['year' => $year]), false);
    $response->assertSee(route('standings', ['year' => $year]), false);
    $response->assertSee(route('standings.predictions', ['year' => $year]), false);
    $response->assertSee(route('standings.teams', ['year' => $year]), false);
    $response->assertSee(route('standings.drivers', ['year' => $year]), false);
    $response->assertSee(route('countries'), false);
});

test('home page standings link returns 200', function () {
    $year = config('f1.current_season');

    $response = $this->get(route('standings', ['year' => $year]));

    $response->assertOk();
});
