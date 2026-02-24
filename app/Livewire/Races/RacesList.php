<?php

namespace App\Livewire\Races;

use App\Exceptions\F1ApiException;
use App\Models\Prediction;
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
            $races = $f1ApiService->getRacesForYear($this->year);
            $this->races = $this->enrichWithPredictions($races);
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

    /**
     * Redirect to the edit page for an existing prediction.
     */
    public function editPrediction(int $predictionId): void
    {
        $this->redirect(route('predictions.edit', ['prediction' => $predictionId]));
    }

    /**
     * Enrich race data with the authenticated user's prediction status for each race.
     *
     * Adds to each race array:
     *   - user_prediction_id: int|null
     *   - user_prediction_status: string|null ('draft'|'submitted'|'locked'|'scored')
     *   - user_prediction_score: int|null
     *   - user_prediction_editable: bool
     *   - user_has_sprint_prediction: bool
     *   - user_sprint_prediction_id: int|null
     *   - user_sprint_prediction_editable: bool
     *
     * @param  array<int, array<string, mixed>>  $races
     * @return array<int, array<string, mixed>>
     */
    private function enrichWithPredictions(array $races): array
    {
        $user = Auth::user();
        if (! $user || empty($races)) {
            return $races;
        }

        // Load all race-type predictions for this user + season in one query
        $predictions = Prediction::where('user_id', $user->id)
            ->where('season', $this->year)
            ->whereIn('type', ['race', 'sprint'])
            ->with('race')
            ->get();

        // Build lookup: race_id => ['race' => Prediction|null, 'sprint' => Prediction|null]
        $byRaceId = [];
        foreach ($predictions as $prediction) {
            $raceId = $prediction->race_id;
            if ($raceId === null) {
                continue;
            }
            $byRaceId[$raceId][$prediction->type] = $prediction;
        }

        // Build a secondary lookup: round => race_id (from DB) for races we already have
        // We only query DB race IDs for rounds that appear in $byRaceId to minimise work
        $raceIdsByRound = Races::where('season', $this->year)
            ->select('id', 'round')
            ->get()
            ->keyBy('round')
            ->map(fn ($r) => $r->id);

        return array_map(function (array $race) use ($byRaceId, $raceIdsByRound): array {
            $round = $race['round'] ?? null;
            if ($round === null) {
                return $race;
            }

            $raceId = $raceIdsByRound[$round] ?? null;
            $racePrediction = $raceId ? ($byRaceId[$raceId]['race'] ?? null) : null;
            $sprintPrediction = $raceId ? ($byRaceId[$raceId]['sprint'] ?? null) : null;

            $race['user_prediction_id'] = $racePrediction?->id;
            $race['user_prediction_status'] = $racePrediction?->status;
            $race['user_prediction_score'] = $racePrediction?->score;
            $race['user_prediction_editable'] = $racePrediction ? $racePrediction->isEditable() : false;
            $race['user_has_sprint_prediction'] = $sprintPrediction !== null;
            $race['user_sprint_prediction_id'] = $sprintPrediction?->id;
            $race['user_sprint_prediction_editable'] = $sprintPrediction ? $sprintPrediction->isEditable() : false;

            return $race;
        }, $races);
    }

    /**
     * Preseason prediction deadline and first race name for the current year.
     *
     * @return array{deadline: \Carbon\Carbon|null, first_race_name: string|null, open: bool}
     */
    public function getPreseasonInfoProperty(): array
    {
        $firstRace = Races::getFirstRaceOfSeason($this->year);
        $deadline = Races::getPreseasonDeadlineForSeason($this->year);

        return [
            'deadline' => $deadline,
            'first_race_name' => $firstRace?->display_name,
            'open' => $deadline !== null && $deadline->isFuture(),
        ];
    }

    /** Whether the season is in the past (no "next" or "future" races). */
    public function getIsSeasonCompletedProperty(): bool
    {
        return $this->year < (int) date('Y');
    }

    /** @return array{past: array, next: array|null, future: array, flat: array} */
    public function getGroupedRacesProperty(): array
    {
        $past = [];
        $next = null;
        $future = [];

        if ($this->isSeasonCompleted) {
            $all = array_values(array_filter($this->races, fn ($r) => is_array($r)));

            return ['past' => $all, 'next' => null, 'future' => [], 'flat' => $all];
        }

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

        return ['past' => $past, 'next' => $next, 'future' => $future, 'flat' => []];
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
     * Each month includes races whose weekend overlaps that month.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public function getRacesByMonthProperty(): array
    {
        $year = $this->year;
        $byMonth = [];
        foreach ($this->races as $race) {
            if (! is_array($race) || empty($race['date'])) {
                continue;
            }
            $weekendStart = $race['weekend_start'] ?? $race['date'];
            $weekendEnd = $race['weekend_end'] ?? $race['date'];
            try {
                $start = Carbon::parse($weekendStart);
                $end = Carbon::parse($weekendEnd);
            } catch (\Throwable) {
                continue;
            }
            for ($m = 1; $m <= 12; $m++) {
                $first = Carbon::createFromDate($year, $m, 1);
                $last = $first->copy()->endOfMonth();
                if ($end->lt($first) || $start->gt($last)) {
                    continue;
                }
                $key = $first->format('Y-m');
                if (! isset($byMonth[$key])) {
                    $byMonth[$key] = [];
                }
                $byMonth[$key][] = $race;
            }
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
