<?php

namespace App\Livewire\Races;

use App\Exceptions\F1ApiException;
use App\Models\Races;
use App\Services\F1ApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RacesList extends Component
{
    public int $year;

    public array $races = [];

    public bool $loading = true;

    public ?string $error = null;

    /** @var 'list'|'calendar' */
    public string $viewMode = 'list';

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
        $user = Auth::user();
        if (! $user || ! $user->hasRole('admin')) {
            throw new HttpException(403, 'Access denied. Admin privileges required to refresh race data.');
        }

        $this->loadRaces();
    }

    /**
     * Redirect to the race detail page.
     */
    public function viewDetails(int $round): void
    {
        $race = Races::where('season', $this->year)->where('round', $round)->first();

        if (! $race) {
            $this->error = 'Race details not found in database. Please sync races first.';

            return;
        }

        $this->redirect(route('race.detail', ['slug' => $race->slug]));
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

    /** @return array{past: array, next: array|null, future: array} */
    public function getGroupedRacesProperty(): array
    {
        $past = [];
        $next = null;
        $future = [];

        foreach ($this->races as $race) {
            if (! is_array($race)) {
                continue;
            }
            $status = $race['status'] ?? 'upcoming';
            if (in_array($status, ['completed', 'cancelled'], true)) {
                $past[] = $race;
            } elseif ($status === 'ongoing') {
                $next = $race;
            } elseif ($status === 'upcoming') {
                if ($next === null) {
                    $next = $race;
                } else {
                    $future[] = $race;
                }
            }
        }

        return ['past' => $past, 'next' => $next, 'future' => $future];
    }

    public function showCalendar(): void
    {
        $this->viewMode = 'calendar';
    }

    public function showList(): void
    {
        $this->viewMode = 'list';
    }

    /**
     * Races grouped by month (Y-m) for calendar view.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public function getRacesByMonthProperty(): array
    {
        $byMonth = [];
        foreach ($this->races as $race) {
            if (! is_array($race) || empty($race['date'])) {
                continue;
            }
            try {
                $date = Carbon::parse($race['date']);
            } catch (\Throwable) {
                continue;
            }
            $key = $date->format('Y-m');
            if (! isset($byMonth[$key])) {
                $byMonth[$key] = [];
            }
            $byMonth[$key][] = $race;
        }
        ksort($byMonth);

        return $byMonth;
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
