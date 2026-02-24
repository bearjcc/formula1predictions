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
        'qualifying_start',
        'sprint_qualifying_start',
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
            'qualifying_start' => 'datetime',
            'sprint_qualifying_start' => 'datetime',
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
     * Whether the season has started (at least one race completed).
     */
    public static function seasonHasStarted(int $season): bool
    {
        return static::where('season', $season)->completed()->exists();
    }

    /**
     * Whether the season has ended (all scheduled races completed; positions are final/clinched).
     */
    public static function seasonHasEnded(int $season): bool
    {
        $total = static::where('season', $season)->count();
        if ($total === 0) {
            return false;
        }

        return static::where('season', $season)->completed()->count() === $total;
    }

    /**
     * Next race (by round) for the given season that still allows predictions.
     * Used when "Start predicting" has no race_id: redirect to this race.
     */
    public static function nextAvailableForPredictions(?int $season = null): ?self
    {
        $season = $season ?? (int) config('f1.current_season');

        return static::where('season', $season)
            ->orderBy('round')
            ->get()
            ->first(fn (self $race): bool => $race->allowsPredictions());
    }

    /**
     * Get the race's full name (stored in DB, for matching).
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->race_name} Grand Prix";
    }

    /**
     * Short name for display and URLs: strip redundant "Formula 1" / "F1" prefix.
     * Never returns null (fallback for missing race_name).
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->race_name ?? '';
        $trimmed = trim($name);
        foreach (['Formula 1 ', 'F1 '] as $prefix) {
            if (stripos($trimmed, $prefix) === 0) {
                $trimmed = trim(substr($trimmed, strlen($prefix)));
                break;
            }
        }

        return $trimmed !== '' ? $trimmed : ('Round '.((int) $this->round));
    }

    /**
     * Get the race's slug for URLs (uses display name).
     */
    public function getSlugAttribute(): string
    {
        return str($this->display_name)
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
     * Check if the race is in the future (or today). Past race dates are never upcoming
     * regardless of stored status, so prediction gates stay correct.
     */
    public function isUpcoming(): bool
    {
        if ($this->date === null) {
            return $this->status === 'upcoming';
        }

        return $this->date->isFuture() || $this->date->isToday();
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
     * Closes 1 hour before qualifying start. Fallback to isUpcoming() when qualifying_start is null.
     */
    public function allowsPredictions(): bool
    {
        if (! $this->isUpcoming()) {
            return false;
        }

        if ($this->qualifying_start === null) {
            return true;
        }

        return now()->lt($this->getRacePredictionDeadline());
    }

    /**
     * Deadline for race predictions: 1 hour before qualifying start. Null when schedule unknown.
     */
    public function getRacePredictionDeadline(): ?\Carbon\Carbon
    {
        if ($this->qualifying_start === null) {
            return null;
        }

        return $this->qualifying_start->copy()->subHour();
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
     * Closes 1 hour before sprint qualifying start. Fallback when sprint_qualifying_start is null.
     */
    public function allowsSprintPredictions(): bool
    {
        if (! $this->hasSprint()) {
            return false;
        }

        if (! $this->isUpcoming()) {
            return false;
        }

        if ($this->sprint_qualifying_start === null) {
            return true;
        }

        return now()->lt($this->getSprintPredictionDeadline());
    }

    /**
     * Deadline for sprint predictions: 1 hour before sprint qualifying start.
     */
    public function getSprintPredictionDeadline(): ?\Carbon\Carbon
    {
        if ($this->sprint_qualifying_start === null) {
            return null;
        }

        return $this->sprint_qualifying_start->copy()->subHour();
    }

    /**
     * Preseason prediction deadline for a season: same as the first race's prediction deadline.
     * Returns null if no first race or no qualifying_start.
     */
    public static function getPreseasonDeadlineForSeason(int $season): ?\Carbon\Carbon
    {
        $firstRace = static::where('season', $season)->orderBy('round')->first();

        return $firstRace?->getRacePredictionDeadline();
    }

    /**
     * First race of the season (by round). Used for preseason deadline and display.
     */
    public static function getFirstRaceOfSeason(int $season): ?self
    {
        return static::where('season', $season)->orderBy('round')->first();
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
