<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Prediction;
use App\Models\Races;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_editable_is_false_when_locked_or_scored(): void
    {
        $locked = Prediction::factory()->locked()->create();
        $scored = Prediction::factory()->scored()->create();

        $this->assertFalse($locked->isEditable());
        $this->assertFalse($scored->isEditable());
    }

    public function test_is_editable_for_race_uses_race_prediction_gates(): void
    {
        $race = Races::factory()->create([
            'season' => 2026,
            'round' => 1,
            'status' => 'upcoming',
            'qualifying_start' => now()->addHours(2),
        ]);

        $prediction = Prediction::factory()->create([
            'type' => 'race',
            'season' => 2026,
            'race_round' => $race->round,
            'race_id' => $race->id,
            'status' => 'draft',
        ]);

        $this->assertTrue($prediction->fresh()->isEditable(), 'Race prediction should be editable before deadline.');

        $race->update(['qualifying_start' => now()->subHour()]);

        $this->assertFalse($prediction->fresh()->isEditable(), 'Race prediction should not be editable after deadline.');
    }

    public function test_is_editable_for_preseason_respects_preseason_deadline(): void
    {
        $season = 2030;

        $firstRace = Races::factory()->create([
            'season' => $season,
            'round' => 1,
            'status' => 'upcoming',
            'qualifying_start' => now()->addHours(3),
        ]);

        $prediction = Prediction::factory()->create([
            'type' => 'preseason',
            'season' => $season,
            'race_round' => null,
            'race_id' => null,
            'status' => 'draft',
        ]);

        $this->assertTrue($prediction->fresh()->isEditable(), 'Preseason prediction should be editable before deadline.');

        $firstRace->update(['qualifying_start' => now()->subHour()]);

        $this->assertFalse($prediction->fresh()->isEditable(), 'Preseason prediction should not be editable after deadline.');
    }

    public function test_submit_sets_status_and_timestamp_when_editable(): void
    {
        $prediction = Prediction::factory()->create([
            'status' => 'draft',
        ]);

        $result = $prediction->submit();

        $this->assertTrue($result);
        $this->assertEquals('submitted', $prediction->status);
        $this->assertNotNull($prediction->submitted_at);
    }

    public function test_submit_returns_false_when_not_editable(): void
    {
        $prediction = Prediction::factory()->locked()->create();

        $result = $prediction->submit();

        $this->assertFalse($result);
        $this->assertEquals('locked', $prediction->status);
    }

    public function test_lock_sets_status_and_timestamp(): void
    {
        $prediction = Prediction::factory()->create([
            'status' => 'submitted',
        ]);

        $result = $prediction->lock();

        $this->assertTrue($result);
        $this->assertEquals('locked', $prediction->status);
        $this->assertNotNull($prediction->locked_at);
    }
}

