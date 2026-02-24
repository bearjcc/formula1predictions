<?php

namespace App\Models;

use App\Services\F1ApiService;
use Illuminate\Database\Eloquent\Collection;
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
     * Drivers competing in a season (from standings, then API fallback).
     * Matches the logic used on the drivers standings page.
     *
     * @return Collection<int, Drivers>
     */
    public static function forSeason(int $season, ?F1ApiService $f1 = null): Collection
    {
        $driverStandings = Standings::getDriverStandings($season, null);
        $entityIds = $driverStandings->pluck('entity_id')->unique()->filter()->values();

        $allDrivers = new Collection;
        if ($entityIds->isNotEmpty()) {
            $byDriverId = static::whereIn('driver_id', $entityIds)->with('team')->get();
            $numericIds = $entityIds->filter(fn ($id) => ctype_digit((string) $id))->values();
            $byId = $numericIds->isNotEmpty()
                ? static::whereIn('id', $numericIds->map(fn ($id) => (int) $id))->with('team')->get()
                : new Collection;
            $allDrivers = $byDriverId->merge($byId)->unique('id')->values();
        }

        if ($allDrivers->isEmpty() && $f1 !== null) {
            try {
                $data = $f1->fetchDriversChampionship($season);
                $entries = $data['drivers_championship'] ?? [];
                if ($entries !== []) {
                    $apiDriverIds = collect($entries)->pluck('driverId')->filter()->unique()->values()->all();
                    $allDrivers = static::whereIn('driver_id', $apiDriverIds)->with('team')->get();
                }
            } catch (\Throwable) {
                // API may not have data for future/past years
            }
        }

        return Collection::make($allDrivers->all());
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
