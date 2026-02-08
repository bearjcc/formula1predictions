<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drivers extends Model
{
    /** @use HasFactory<\Database\Factories\DriversFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'driver_id',
        'name',
        'surname',
        'nationality',
        'url',
        'driver_number',
        'description',
        'photo_url',
        'helmet_url',
        'date_of_birth',
        'website',
        'twitter',
        'instagram',
        'world_championships',
        'race_wins',
        'podiums',
        'pole_positions',
        'fastest_laps',
        'points',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'world_championships' => 'integer',
            'race_wins' => 'integer',
            'podiums' => 'integer',
            'pole_positions' => 'integer',
            'fastest_laps' => 'integer',
            'points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the team that the driver belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }

    /**
     * Get the standings for this driver.
     */
    public function standings(): HasMany
    {
        return $this->hasMany(Standings::class, 'entity_id', 'driver_id')
            ->where('type', 'drivers');
    }

    /**
     * Get the driver's current season standings.
     */
    public function getCurrentStandings(int $season): ?Standings
    {
        return $this->standings()
            ->where('season', $season)
            ->whereNull('round')
            ->first();
    }

    /**
     * Get the driver's race-by-race standings for a season.
     */
    public function getSeasonStandings(int $season): \Illuminate\Database\Eloquent\Collection
    {
        return $this->standings()
            ->where('season', $season)
            ->whereNotNull('round')
            ->orderBy('round')
            ->get();
    }

    /**
     * Get the driver's total points for a season.
     */
    public function getSeasonPoints(int $season): float
    {
        $standing = $this->getCurrentStandings($season);

        return $standing ? $standing->points : 0.0;
    }

    /**
     * Get the driver's position in the championship for a season.
     */
    public function getSeasonPosition(int $season): ?int
    {
        $standing = $this->getCurrentStandings($season);

        return $standing ? $standing->position : null;
    }

    /**
     * Scope to get only active drivers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the driver's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->surname}";
    }

    /**
     * Get the driver's slug for URLs.
     */
    public function getSlugAttribute(): string
    {
        return str($this->getFullNameAttribute())
            ->lower()
            ->replace([' ', '&', '-'], '-')
            ->slug();
    }

    /**
     * Get the driver's age.
     */
    public function getAgeAttribute(): ?int
    {
        if (! $this->date_of_birth) {
            return null;
        }

        return $this->date_of_birth->age;
    }

    /**
     * Get the driver's initials.
     */
    public function getInitialsAttribute(): string
    {
        return str($this->name)->substr(0, 1).str($this->surname)->substr(0, 1);
    }
}
