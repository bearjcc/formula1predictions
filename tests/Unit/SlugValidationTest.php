<?php

it('validates slugs correctly', function () {
    expect('valid-slug')->toBeSlug();
    expect('another-valid-slug-123')->toBeSlug();
    expect('simple')->toBeSlug();
    expect('123-numbers')->toBeSlug();
});

it('rejects invalid slugs', function () {
    expect('Invalid Slug')->not->toBeSlug();
    expect('invalid slug with spaces')->not->toBeSlug();
    expect('invalid-slug-with-UPPERCASE')->not->toBeSlug();
    expect('invalid-slug-with-special-chars!')->not->toBeSlug();
    expect('-starts-with-dash')->not->toBeSlug();
    expect('ends-with-dash-')->not->toBeSlug();
    expect('double--dash')->not->toBeSlug();
});

it('validates race slugs', function () {
    $validRaceSlugs = [
        'australian-grand-prix',
        'monaco-grand-prix',
        'british-grand-prix-2024',
        'f1-race-1',
    ];

    foreach ($validRaceSlugs as $slug) {
        expect($slug)->toBeSlug();
    }
});

it('validates driver slugs', function () {
    $validDriverSlugs = [
        'lewis-hamilton',
        'max-verstappen',
        'charles-leclerc',
        'driver-44',
    ];

    foreach ($validDriverSlugs as $slug) {
        expect($slug)->toBeSlug();
    }
});
