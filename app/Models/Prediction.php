<?php

namespace App\Models;

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
     * Calculate the prediction score based on the scoring system.
     */
    public function calculateScore(): int
    {
        if ($this->type !== 'race' || empty($this->prediction_data) || !$this->race) {
            return 0;
        }

        $predictedOrder = $this->prediction_data['driver_order'] ?? [];
        $actualResults = $this->race->getResultsArray();
        
        if (empty($actualResults)) {
            return 0;
        }

        $score = 0;
        $totalDrivers = count($predictedOrder);

        foreach ($predictedOrder as $position => $driverId) {
            $actualPosition = $this->findDriverPosition($driverId, $actualResults);
            
            if ($actualPosition !== null) {
                $positionDiff = abs($position - $actualPosition);
                $score += $this->getPositionScore($positionDiff);
            }
        }

        // Add bonus points for perfect predictions
        if ($score === $totalDrivers * 25) {
            $score += 50; // Perfect prediction bonus
        }

        return $score;
    }

    /**
     * Find the actual position of a driver in race results.
     */
    private function findDriverPosition(string $driverId, array $results): ?int
    {
        foreach ($results as $position => $result) {
            if (($result['driver']['driverId'] ?? '') === $driverId) {
                return $position;
            }
        }
        return null;
    }

    /**
     * Get the score for a position difference.
     */
    private function getPositionScore(int $positionDiff): int
    {
        return match ($positionDiff) {
            0 => 25,  // Correct prediction
            1 => 18,  // 1 position away
            2 => 15,  // 2 positions away
            3 => 12,  // 3 positions away
            4 => 10,  // 4 positions away
            5 => 8,   // 5 positions away
            6 => 6,   // 6 positions away
            7 => 4,   // 7 positions away
            8 => 2,   // 8 positions away
            9 => 1,   // 9 positions away
            default => max(-25, -$positionDiff), // 10+ positions away
        };
    }

    /**
     * Calculate the prediction accuracy percentage.
     */
    public function calculateAccuracy(): float
    {
        if ($this->type !== 'race' || empty($this->prediction_data) || !$this->race) {
            return 0.0;
        }

        $predictedOrder = $this->prediction_data['driver_order'] ?? [];
        $actualResults = $this->race->getResultsArray();
        
        if (empty($actualResults) || empty($predictedOrder)) {
            return 0.0;
        }

        $correctPredictions = 0;
        $totalPredictions = count($predictedOrder);

        foreach ($predictedOrder as $position => $driverId) {
            $actualPosition = $this->findDriverPosition($driverId, $actualResults);
            
            if ($actualPosition !== null && $position === $actualPosition) {
                $correctPredictions++;
            }
        }

        return ($correctPredictions / $totalPredictions) * 100;
    }

    /**
     * Check if prediction is still editable.
     */
    public function isEditable(): bool
    {
        if ($this->status === 'locked' || $this->status === 'scored') {
            return false;
        }

        if ($this->type === 'race' && $this->race) {
            return $this->race->allowsPredictions();
        }

        return true;
    }

    /**
     * Submit the prediction.
     */
    public function submit(): bool
    {
        if (!$this->isEditable()) {
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

        $this->score = $this->calculateScore();
        $this->accuracy = $this->calculateAccuracy();
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
}
