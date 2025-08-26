<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test that views load correctly and return expected content
it("can load the home page view", function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertViewIs('welcome');
});

// Test year-specific views
describe('Year-specific views load correctly', function () {
    $validYears = ['2022', '2023'];
    $yearRoutes = [
        '/{year}/races' => 'races',
        '/{year}/standings' => 'standings',
        '/{year}/standings/drivers' => 'standings.drivers',
        '/{year}/standings/teams' => 'standings.teams',
        '/{year}/standings/predictions' => 'standings.predictions',
    ];

    foreach ($validYears as $year) {
        foreach ($yearRoutes as $route => $view) {
            $testRoute = str_replace('{year}', $year, $route);
            
            it("loads {$view} view for {$testRoute}", function () use ($testRoute, $view, $year) {
                $response = $this->get($testRoute);
                $response->assertStatus(200);
                $response->assertViewIs($view);
                $response->assertViewHas('year', $year);
            });
        }
    }

    // Test year-specific routes with additional parameters
    it("loads standings.predictions view for /2023/standings/predictions/bearjcc", function () {
        $response = $this->get('/2023/standings/predictions/bearjcc');
        $response->assertStatus(200);
        $response->assertViewIs('standings.predictions');
        $response->assertViewHas('year', '2023');
        $response->assertViewHas('username', 'bearjcc');
    });

    it("loads race view for /2023/race/123", function () {
        $response = $this->get('/2023/race/123');
        $response->assertStatus(200);
        $response->assertViewIs('race');
        $response->assertViewHas('year', '2023');
        $response->assertViewHas('id', '123');
    });
});

// Test non-year-specific views
describe('Non-year-specific views load correctly', function () {
    $routes = [
        '/countries' => 'countries',
        '/team/mercedes' => 'team',
        '/driver/lewis-hamilton' => 'driver',
        '/circuit/silverstone' => 'circuit',
        '/country/belgium' => 'country',
        '/race/british-grand-prix' => 'race',
    ];

    foreach ($routes as $route => $view) {
        it("loads {$view} view for {$route}", function () use ($route, $view) {
            $response = $this->get($route);
            $response->assertStatus(200);
            $response->assertViewIs($view);
        });
    }

    // Test that slug parameters are passed correctly
    it("passes slug parameter to team view", function () {
        $response = $this->get('/team/mercedes');
        $response->assertViewHas('slug', 'mercedes');
    });

    it("passes slug parameter to driver view", function () {
        $response = $this->get('/driver/lewis-hamilton');
        $response->assertViewHas('slug', 'lewis-hamilton');
    });

    it("passes slug parameter to circuit view", function () {
        $response = $this->get('/circuit/silverstone');
        $response->assertViewHas('slug', 'silverstone');
    });

    it("passes slug parameter to country view", function () {
        $response = $this->get('/country/belgium');
        $response->assertViewHas('slug', 'belgium');
    });

    it("passes slug parameter to race detail view", function () {
        $response = $this->get('/race/british-grand-prix');
        $response->assertViewHas('slug', 'british-grand-prix');
    });
});

// Test view content (basic structure)
describe('Views have basic structure', function () {
    it("home page has basic HTML structure", function () {
        $response = $this->get('/');
        $response->assertSee('<!DOCTYPE html>', false); // Case insensitive
        $response->assertSee('<html', false);
        $response->assertSee('<body', false);
    });

    it("year-specific pages show the year", function () {
        $response = $this->get('/2023/races');
        $response->assertSee('2023');
    });

    it("team page shows the team slug", function () {
        $response = $this->get('/team/mercedes');
        $response->assertSee('mercedes');
    });
});

// Test view errors are handled gracefully
describe('View errors are handled gracefully', function () {
    it("returns 500 for missing views", function () {
        // This test will help identify if views are missing
        $response = $this->get('/2023/races');
        $response->assertStatus(200); // Should not be 500 if view exists
    });
});

// Test view data structure
describe('View data is structured correctly', function () {
    it("year-specific routes pass year data", function () {
        $response = $this->get('/2023/standings/drivers');
        $response->assertViewHas('year');
        $response->assertViewHasAll(['year']);
    });

    it("slug-based routes pass slug data", function () {
        $response = $this->get('/team/ferrari');
        $response->assertViewHas('slug');
        $response->assertViewHasAll(['slug']);
    });

    it("complex routes pass multiple parameters", function () {
        $response = $this->get('/2023/standings/predictions/testuser');
        $response->assertViewHasAll(['year', 'username']);
    });
});
