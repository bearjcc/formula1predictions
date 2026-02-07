<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Races extends Model
{
    /** @use HasFactory<\Database\Factories\RacesFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'season',
        'round',
        'race_name',
        'date',
        'time',
        'circuit_id',
        'circuit_api_id',
        'circuit_name',
        'circuit_url',
        'country',
        'locality',
        'circuit_length',
        'laps',
        'weather',
        'temperature',
        'status',
        'has_sprint',
        'is_special_event',
        'half_points',
        'results',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'datetime',
            'circuit_length' => 'decimal:3',
            'laps' => 'integer',
            'temperature' => 'decimal:1',
            'has_sprint' => 'boolean',
            'is_special_event' => 'boolean',
            'half_points' => 'boolean',
            'results' => 'array',
        ];
    }

    /**
     * Get the circuit for this race.
     */
    public function circuit(): BelongsTo
    {
        return $this->belongsTo(Circuits::class, 'circuit_id');
    }

    /**
     * Get the predictions for this race.
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'race_id', 'id')
            ->where('type', 'race');
    }

    /**
     * Get the sprint predictions for this race.
     */
    public function sprintPredictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'race_id', 'id')
            ->where('type', 'sprint');
    }

    /**
     * Get the standings for this race.
     */
    public function standings(): HasMany
    {
        return $this->hasMany(Standings::class, 'round', 'round')
            ->where('standings.season', $this->season);
    }

    /**
     * Scope to get races for a specific season.
     */
    public function scopeForSeason($query, int $season)
    {
        return $query->where('season', $season);
    }

    /**
     * Scope to get races by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get upcoming races.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    /**
     * Scope to get completed races.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get the race's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->race_name} Grand Prix";
    }

    /**
     * Get the race's slug for URLs.
     */
    public function getSlugAttribute(): string
    {
        return str($this->race_name)
            ->lower()
            ->replace([' ', '&', '-'], '-')
            ->slug();
    }

    /**
     * Get the race's formatted date and time.
     */
    public function getFormattedDateTimeAttribute(): string
    {
        $date = $this->date->format('F j, Y');

        if ($this->time) {
            $time = $this->time->format('g:i A T');

            return "{$date} at {$time}";
        }

        return $date;
    }

    /**
     * Check if the race is in the future.
     */
    public function isUpcoming(): bool
    {
        return $this->status === 'upcoming' || ($this->date !== null && $this->date->isFuture());
    }

    /**
     * Check if the race is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' || ! empty($this->results);
    }

    /**
     * Check if predictions are still allowed for this race.
     */
    public function allowsPredictions(): bool
    {
        // Predictions are allowed until the race starts
        return $this->isUpcoming();
    }

    /**
     * Check if this race has a sprint session.
     */
    public function hasSprint(): bool
    {
        return (bool) $this->has_sprint;
    }

    /**
     * Check if sprint predictions are still allowed for this race.
     */
    public function allowsSprintPredictions(): bool
    {
        if (! $this->hasSprint()) {
            return false;
        }

        return $this->isUpcoming();
    }

    /**
     * Get the race results as an array.
     */
    public function getResultsArray(): array
    {
        if (is_string($this->results)) {
            return json_decode($this->results, true) ?? [];
        }

        return $this->results ?? [];
    }

    /**
     * Get the winner of the race.
     */
    public function getWinner(): ?array
    {
        $results = $this->getResultsArray();

        return $results[0] ?? null;
    }

    /**
     * Get the podium finishers.
     */
    public function getPodium(): array
    {
        $results = $this->getResultsArray();

        return array_slice($results, 0, 3);
    }
}
