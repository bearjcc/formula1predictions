<?php

use App\Models\Countries;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('countries index returns 200', function () {
    $this->get(route('countries'))->assertOk();
});

test('countries index shows real country data from database', function () {
    Countries::factory()->create([
        'name' => 'United Kingdom',
        'code' => 'GBR',
        'world_championships_won' => 20,
        'f1_races_hosted' => 303,
        'is_active' => true,
    ]);
    Countries::factory()->create([
        'name' => 'Germany',
        'code' => 'DEU',
        'world_championships_won' => 12,
        'f1_races_hosted' => 179,
        'is_active' => true,
    ]);

    $response = $this->get(route('countries'));

    $response->assertOk();
    $response->assertSee('United Kingdom');
    $response->assertSee('Germany');
    $response->assertSee('20');
    $response->assertSee('303');
    $response->assertSee('12');
    $response->assertSee('179');
});

test('countries index does not show hardcoded stub data when database is empty', function () {
    $response = $this->get(route('countries'));

    $response->assertOk();
    $response->assertDontSee('Showing 1-6 of 25 countries');
    $response->assertSee('No countries match your filters', false);
});

test('countries index pagination shows correct range and total', function () {
    Countries::factory()->count(12)->create();

    $response = $this->get(route('countries'));

    $response->assertOk();
    $response->assertSee('Showing 1-9 of 12 countries');
});

test('countries index search filters by name', function () {
    Countries::factory()->create(['name' => 'United Kingdom', 'code' => 'GBR']);
    Countries::factory()->create(['name' => 'Germany', 'code' => 'DEU']);
    Countries::factory()->create(['name' => 'Brazil', 'code' => 'BRA']);

    $response = $this->get(route('countries', ['search' => 'Germany']));

    $response->assertOk();
    $response->assertSee('Germany');
    $response->assertDontSee('United Kingdom');
    $response->assertDontSee('Brazil');
});

test('countries index status filter shows only active countries', function () {
    Countries::factory()->create(['name' => 'Active Country', 'code' => 'ACT', 'is_active' => true]);
    Countries::factory()->create(['name' => 'Historic Country', 'code' => 'HIS', 'is_active' => false]);

    $response = $this->get(route('countries', ['status' => 'active']));

    $response->assertOk();
    $response->assertSee('Active Country');
    $response->assertDontSee('Historic Country');
});

test('countries index championships filter filters by world_championships_won range', function () {
    Countries::factory()->create(['name' => 'Low', 'code' => 'LOW', 'world_championships_won' => 3]);
    Countries::factory()->create(['name' => 'Mid', 'code' => 'MID', 'world_championships_won' => 8]);
    Countries::factory()->create(['name' => 'High', 'code' => 'HIG', 'world_championships_won' => 15]);

    $response = $this->get(route('countries', ['championships' => '6-10']));

    $response->assertOk();
    $response->assertSee('Mid');
    $response->assertDontSee('Low');
    $response->assertDontSee('High');
});

test('countries index view details links to country detail page', function () {
    $country = Countries::factory()->create(['name' => 'Belgium', 'code' => 'BEL']);

    $response = $this->get(route('countries'));

    $response->assertOk();
    $response->assertSee(route('country', ['slug' => $country->slug]), false);
});
