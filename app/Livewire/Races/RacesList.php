<?php

namespace App\Livewire\Races;

use App\Services\F1ApiService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class RacesList extends Component
{
    public int $year;

    public array $races = [];

    public bool $loading = true;

    public ?string $error = null;

    public string $statusFilter = '';

    public string $searchQuery = '';

    public function mount(int $year)
    {
        $this->year = $year;
        $this->loadRaces();
    }

    public function loadRaces(): void
    {
        $this->loading = true;
        $this->error = null;

        try {
            $f1ApiService = app(F1ApiService::class);
            $this->races = $f1ApiService->getRacesForYear($this->year);
        } catch (\Exception $e) {
            $this->error = 'Failed to load races: '.$e->getMessage();
            Log::error('Failed to load races for year '.$this->year.': '.$e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    public function refreshRaces(): void
    {
        $this->loadRaces();
    }

    public function getFilteredRacesProperty(): array
    {
        $filtered = $this->races;

        // Apply status filter
        if (! empty($this->statusFilter)) {
            $filtered = array_filter($filtered, function ($race) {
                return $race['status'] === $this->statusFilter;
            });
        }

        // Apply search filter
        if (! empty($this->searchQuery)) {
            $filtered = array_filter($filtered, function ($race) {
                $searchLower = strtolower($this->searchQuery);

                return str_contains(strtolower($race['raceName']), $searchLower) ||
                       str_contains(strtolower($race['circuit']['circuitName']), $searchLower) ||
                       str_contains(strtolower($race['circuit']['country']), $searchLower);
            });
        }

        return array_values($filtered);
    }

    public function getStatusBadgeVariant(string $status): string
    {
        return match ($status) {
            'completed' => 'tinted',
            'upcoming' => 'outline',
            'ongoing' => 'filled',
            'cancelled' => 'filled',
            default => 'outline'
        };
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'upcoming' => 'border-blue-200 text-blue-700 dark:border-blue-700 dark:text-blue-300',
            'ongoing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'border-gray-200 text-gray-700 dark:border-gray-700 dark:text-gray-300'
        };
    }

    public function render()
    {
        return view('livewire.races.races-list');
    }
}
