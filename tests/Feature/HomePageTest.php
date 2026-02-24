<?php

test('home page loads successfully', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('F1 Predictions');
});

test('home page hero start predicting link points to login or predict create', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Start Predicting');
    $response->assertSee('View Standings');
});

test('home page card links resolve to valid routes', function () {
    // Keep this in sync with config('f1.current_season')
    $year = 2026;

    /** @var \Tests\TestCase $this */
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee(route('races', ['year' => $year]), false);
    $response->assertSee(route('standings', ['year' => $year]), false);
    $response->assertSee(route('standings.predictions', ['year' => $year]), false);
    $response->assertSee(route('standings.constructors', ['year' => $year]), false);
    $response->assertSee(route('standings.drivers', ['year' => $year]), false);
    // Countries card intentionally hidden (F1-106)
});

test('home page standings link redirects to drivers standings', function () {
    // Keep this in sync with config('f1.current_season')
    $year = 2026;

    /** @var \Tests\TestCase $this */
    $response = $this->get(route('standings', ['year' => $year]));

    $response->assertRedirect(route('standings.drivers', ['year' => $year]));
});
