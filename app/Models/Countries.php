<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Countries extends Model
{
    /** @use HasFactory<\Database\Factories\CountriesFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'flag_url',
        'description',
        'f1_races_hosted',
        'world_championships_won',
        'drivers_count',
        'teams_count',
        'circuits_count',
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
            'f1_races_hosted' => 'integer',
            'world_championships_won' => 'integer',
            'drivers_count' => 'integer',
            'teams_count' => 'integer',
            'circuits_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the drivers from this country.
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(Drivers::class, 'nationality', 'name');
    }

    /**
     * Get the teams from this country.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Teams::class, 'nationality', 'name');
    }

    /**
     * Get the circuits in this country.
     */
    public function circuits(): HasMany
    {
        return $this->hasMany(Circuits::class, 'country', 'name');
    }

    /**
     * Scope to get only active countries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the country's slug for URLs.
     */
    public function getSlugAttribute(): string
    {
        return str($this->name)
            ->lower()
            ->replace([' ', '&', '-'], '-')
            ->slug();
    }

    /**
     * Get the country's flag URL.
     */
    public function getFlagUrlAttribute(): string
    {
        $url = $this->attributes['flag_url'] ?? null;

        if ($url) {
            return $url;
        }

        $code = $this->attributes['code'] ?? '';

        return $code ? 'https://flagcdn.com/'.strtolower($code).'.svg' : '';
    }

    /**
     * Get the country's F1 statistics.
     */
    public function getF1Stats(): array
    {
        return [
            'races_hosted' => $this->f1_races_hosted,
            'world_championships' => $this->world_championships_won,
            'drivers' => $this->drivers_count,
            'teams' => $this->teams_count,
            'circuits' => $this->circuits_count,
        ];
    }
}
