<?php

namespace App\Livewire\Races;

use App\Exceptions\F1ApiException;
use App\Models\Races;
use App\Services\F1ApiService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;

class RacesList extends Component
{
    public int $year;

    public array $races = [];

    public bool $loading = true;

    public ?string $error = null;

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    #[Url(as: 'search', except: '')]
    public string $searchQuery = '';

    public function mount(int $year)
    {
        $this->year = $year;
        $this->statusFilter = request()->query('status', $this->statusFilter);
        $this->searchQuery = request()->query('search', $this->searchQuery);
        $this->loadRaces();
    }

    public function loadRaces(): void
    {
        $this->loading = true;
        $this->error = null;

        try {
            $f1ApiService = app(F1ApiService::class);
            $this->races = $f1ApiService->getRacesForYear($this->year);
        } catch (F1ApiException $e) {
            $this->error = 'We\'re having trouble loading race data right now. Please try again in a few moments.';
            Log::error('F1 API races list failed', array_merge(
                ['message' => $e->getMessage()],
                $e->getLogContext()
            ));
        } catch (\Throwable $e) {
            $this->error = 'We\'re having trouble loading race data right now. Please try again in a few moments.';
            Log::error('F1 API races list failed', [
                'year' => $this->year,
                'message' => $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function refreshRaces(): void
    {
        $this->loadRaces();
    }

    /**
     * Redirect to prediction creation with race context
     */
    public function makePrediction(int $round): void
    {
        $race = Races::where('season', $this->year)->where('round', $round)->first();

        if (! $race) {
            $this->error = 'Race details not found in database. Please sync races first.';

            return;
        }

        $this->redirect(route('predict.create', ['race_id' => $race->id]));
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
