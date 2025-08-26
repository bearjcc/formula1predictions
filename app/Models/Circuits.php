<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Circuits extends Model
{
    /** @use HasFactory<\Database\Factories\CircuitsFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'circuit_id',
        'circuit_name',
        'url',
        'country',
        'locality',
        'latitude',
        'longitude',
        'circuit_length',
        'laps',
        'description',
        'photo_url',
        'map_url',
        'capacity',
        'first_grand_prix',
        'lap_record_driver',
        'lap_record_time',
        'lap_record_year',
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
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'circuit_length' => 'decimal:3',
            'laps' => 'integer',
            'capacity' => 'integer',
            'lap_record_year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the races held at this circuit.
     */
    public function races(): HasMany
    {
        return $this->hasMany(Races::class, 'circuit_id', 'circuit_id');
    }

    /**
     * Scope to get only active circuits.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get circuits by country.
     */
    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Get the circuit's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->circuit_name;
    }

    /**
     * Get the circuit's slug for URLs.
     */
    public function getSlugAttribute(): string
    {
        return str($this->circuit_name)
            ->lower()
            ->replace([' ', '&', '-'], '-')
            ->slug();
    }

    /**
     * Get the circuit's location string.
     */
    public function getLocationAttribute(): string
    {
        if ($this->locality && $this->country) {
            return "{$this->locality}, {$this->country}";
        }
        
        return $this->country ?? $this->locality ?? 'Unknown';
    }

    /**
     * Get the circuit's coordinates as an array.
     */
    public function getCoordinatesAttribute(): array
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
        ];
    }

    /**
     * Get the circuit's lap record information.
     */
    public function getLapRecordAttribute(): array
    {
        return [
            'driver' => $this->lap_record_driver,
            'time' => $this->lap_record_time,
            'year' => $this->lap_record_year,
        ];
    }

    /**
     * Get the circuit's statistics.
     */
    public function getStats(): array
    {
        return [
            'length' => $this->circuit_length,
            'laps' => $this->laps,
            'capacity' => $this->capacity,
            'first_grand_prix' => $this->first_grand_prix,
            'lap_record' => $this->getLapRecordAttribute(),
        ];
    }

    /**
     * Get the most recent race at this circuit.
     */
    public function getLatestRace(): ?Races
    {
        return $this->races()
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Get the next race at this circuit.
     */
    public function getNextRace(): ?Races
    {
        return $this->races()
            ->where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->first();
    }
}
