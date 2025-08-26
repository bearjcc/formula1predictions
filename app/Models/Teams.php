<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teams extends Model
{
    /** @use HasFactory<\Database\Factories\TeamsFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'team_id',
        'team_name',
        'nationality',
        'url',
        'description',
        'logo_url',
        'website',
        'team_principal',
        'technical_director',
        'chassis',
        'power_unit',
        'base_location',
        'founded',
        'world_championships',
        'race_wins',
        'podiums',
        'pole_positions',
        'fastest_laps',
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
            'founded' => 'integer',
            'world_championships' => 'integer',
            'race_wins' => 'integer',
            'podiums' => 'integer',
            'pole_positions' => 'integer',
            'fastest_laps' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the drivers for this team.
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(Drivers::class, 'team_id');
    }

    /**
     * Get the standings for this team.
     */
    public function standings(): HasMany
    {
        return $this->hasMany(Standings::class, 'entity_id', 'team_id')
            ->where('type', 'constructors');
    }

    /**
     * Get the team's current season standings.
     */
    public function getCurrentStandings(int $season): ?Standings
    {
        return $this->standings()
            ->where('season', $season)
            ->whereNull('round')
            ->first();
    }

    /**
     * Get the team's race-by-race standings for a season.
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
     * Get the team's total points for a season.
     */
    public function getSeasonPoints(int $season): float
    {
        $standing = $this->getCurrentStandings($season);
        return $standing ? $standing->points : 0.0;
    }

    /**
     * Get the team's position in the championship for a season.
     */
    public function getSeasonPosition(int $season): ?int
    {
        $standing = $this->getCurrentStandings($season);
        return $standing ? $standing->position : null;
    }

    /**
     * Scope to get only active teams.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the team's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->team_name;
    }

    /**
     * Get the team's slug for URLs.
     */
    public function getSlugAttribute(): string
    {
        return str($this->team_name)
            ->lower()
            ->replace([' ', '&', '-'], '-')
            ->slug();
    }
}
