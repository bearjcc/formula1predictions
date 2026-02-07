<?php

namespace App\Models;

use App\Services\ScoringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    /** @use HasFactory<\Database\Factories\PredictionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'season',
        'race_round',
        'race_id',
        'prediction_data',
        'score',
        'accuracy',
        'status',
        'submitted_at',
        'locked_at',
        'scored_at',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'prediction_data' => 'array',
            'score' => 'integer',
            'accuracy' => 'decimal:2',
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
            'scored_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the prediction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the race for this prediction.
     */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Races::class, 'race_id');
    }

    /**
     * Check if prediction is still editable.
     */
    public function isEditable(): bool
    {
        if ($this->status === 'locked' || $this->status === 'scored') {
            return false;
        }

        if (in_array($this->type, ['race', 'sprint'], true) && $this->race) {
            if ($this->type === 'sprint') {
                return $this->race->allowsSprintPredictions();
            }

            return $this->race->allowsPredictions();
        }

        return true;
    }

    /**
     * Submit the prediction.
     */
    public function submit(): bool
    {
        if (! $this->isEditable()) {
            return false;
        }

        $this->status = 'submitted';
        $this->submitted_at = now();

        return $this->save();
    }

    /**
     * Lock the prediction (no more edits allowed).
     */
    public function lock(): bool
    {
        $this->status = 'locked';
        $this->locked_at = now();

        return $this->save();
    }

    /**
     * Score the prediction.
     */
    public function score(): bool
    {
        if ($this->status === 'scored') {
            return true;
        }

        /** @var ScoringService $service */
        $service = app(ScoringService::class);

        if ($this->race && $this->type === 'race') {
            $score = $service->calculatePredictionScore($this, $this->race);

            $service->savePredictionScore($this, $score);

            $this->refresh();

            return true;
        }

        if ($this->race && $this->type === 'sprint') {
            $score = $service->calculateSprintPredictionScore($this, $this->race);

            $service->savePredictionScore($this, $score);

            $this->refresh();

            return true;
        }

        $this->score = 0;
        $this->accuracy = 0.0;
        $this->status = 'scored';
        $this->scored_at = now();

        return $this->save();
    }

    /**
     * Scope to get predictions by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get predictions for a specific season.
     */
    public function scopeForSeason($query, int $season)
    {
        return $query->where('season', $season);
    }

    /**
     * Scope to get predictions by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the prediction data as an array.
     */
    public function getPredictionDataArray(): array
    {
        return $this->prediction_data ?? [];
    }

    /**
     * Get the predicted driver order for race predictions.
     */
    public function getPredictedDriverOrder(): array
    {
        return $this->getPredictionDataArray()['driver_order'] ?? [];
    }

    /**
     * Get the predicted fastest lap for race predictions.
     */
    public function getPredictedFastestLap(): ?string
    {
        return $this->getPredictionDataArray()['fastest_lap'] ?? null;
    }

    /**
     * Get DNF wager predictions (driver IDs predicted to DNF). Race predictions only.
     *
     * @return list<string|int>
     */
    public function getDnfPredictions(): array
    {
        $raw = $this->getPredictionDataArray()['dnf_predictions'] ?? [];

        return is_array($raw) ? array_values($raw) : [];
    }

    /**
     * Get predicted team order for preseason/midseason.
     *
     * @return list<int>
     */
    public function getTeamOrder(): array
    {
        $raw = $this->getPredictionDataArray()['team_order'] ?? [];

        return is_array($raw) ? array_map('intval', array_values($raw)) : [];
    }

    /**
     * Get predicted driver championship order for preseason/midseason.
     *
     * @return list<int>
     */
    public function getDriverChampionshipOrder(): array
    {
        $raw = $this->getPredictionDataArray()['driver_championship'] ?? [];

        return is_array($raw) ? array_map('intval', array_values($raw)) : [];
    }
}
