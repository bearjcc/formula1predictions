<?php

declare(strict_types=1);

use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// F1-082: 2026 standings and prediction standings use URL year and real data (no mock/fake users)
describe('2026 standings pages', function () {
    test('standings index for 2026 returns 200 and shows year in heading', function () {
        $response = $this->get('/2026/standings');

        $response->assertOk();
        $response->assertSee('2026 Standings', false);
        $response->assertSee(route('standings.drivers', ['year' => 2026]), false);
        $response->assertSee(route('standings.predictions', ['year' => 2026]), false);
    });

    test('prediction standings for 2026 returns 200 and does not show fake usernames', function () {
        $response = $this->get('/2026/standings/predictions');

        $response->assertOk();
        $response->assertSee('2026', false);
        $response->assertSee('Prediction Standings', false);
        $response->assertDontSee('F1Expert', false);
        $response->assertDontSee('RacingFan2023', false);
        $response->assertDontSee('PredictorPro', false);
    });

    test('prediction standings for 2026 shows real users with predictions', function () {
        $user = User::factory()->create(['name' => 'RealPredictor2026']);
        Prediction::factory()->create([
            'user_id' => $user->id,
            'season' => 2026,
            'status' => 'scored',
            'score' => 50,
        ]);

        $response = $this->get('/2026/standings/predictions');

        $response->assertOk();
        $response->assertSee('RealPredictor2026', false);
        $response->assertSee('2026', false);
    });
});
