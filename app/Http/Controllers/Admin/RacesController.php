<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ScoreRacePredictionsJob;
use App\Models\Races;
use App\Services\ScoringService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RacesController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    public function __construct(
        private readonly ScoringService $scoringService,
    ) {}

    public function index(): View|RedirectResponse
    {
        try {
            $races = Races::orderBy('date', 'desc')->paginate(20);

            return view('admin.races', compact('races'));
        } catch (\Throwable $e) {
            Log::error('Admin\RacesController@index failed', ['exception' => $e]);

            return redirect()->route('admin.dashboard')->with('error', 'Unable to load races. Please try again.');
        }
    }

    public function scoring(): View|RedirectResponse
    {
        try {
            $races = Races::withCount(['predictions' => function ($query) {
                $query->whereIn('status', ['submitted', 'locked']);
            }])
                ->orderBy('date', 'desc')
                ->paginate(20);

            return view('admin.scoring', compact('races'));
        } catch (\Throwable $e) {
            Log::error('Admin\RacesController@scoring failed', ['exception' => $e]);

            return redirect()->route('admin.dashboard')->with('error', 'Unable to load scoring page. Please try again.');
        }
    }

    public function score(Request $request, Races $race): RedirectResponse
    {
        $this->authorize('manageResults', $race);

        try {
            $results = $this->scoringService->scoreRacePredictions($race);

            $message = "Successfully scored {$results['scored_predictions']} predictions for {$results['total_score']} total points.";

            if ($results['failed_predictions'] > 0) {
                $message .= " {$results['failed_predictions']} predictions failed to score.";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Admin\RacesController@score failed', ['race_id' => $race->id, 'exception' => $e]);

            return redirect()->back()->with('error', 'Failed to score race. Please try again or check the logs.');
        }
    }

    public function queueScoring(Request $request, Races $race): RedirectResponse
    {
        $this->authorize('manageResults', $race);

        try {
            ScoreRacePredictionsJob::dispatch($race->id, $request->boolean('force_update'));

            return redirect()->back()->with('success', "Scoring job queued for {$race->display_name}");
        } catch (\Throwable $e) {
            Log::error('Admin\RacesController@queueScoring failed', ['race_id' => $race->id, 'exception' => $e]);

            return redirect()->back()->with('error', 'Failed to queue scoring. Please try again.');
        }
    }

    public function handleDriverSubstitutions(Request $request, Races $race): RedirectResponse
    {
        $this->authorize('manageResults', $race);

        $request->validate([
            'substitutions' => 'required|array',
            'substitutions.*.old_driver_id' => 'required|string',
            'substitutions.*.new_driver_id' => 'required|string',
        ]);

        try {
            $substitutions = [];
            foreach ($request->input('substitutions') as $sub) {
                $substitutions[$sub['old_driver_id']] = $sub['new_driver_id'];
            }

            $this->scoringService->handleDriverSubstitutions($race, $substitutions);

            return redirect()->back()->with('success', 'Driver substitutions applied successfully');
        } catch (\Throwable $e) {
            Log::error('Admin\RacesController@handleDriverSubstitutions failed', ['race_id' => $race->id, 'exception' => $e]);

            return redirect()->back()->with('error', 'Failed to apply driver substitutions. Please try again.');
        }
    }

    public function cancel(Request $request, Races $race): RedirectResponse
    {
        $this->authorize('manageResults', $race);

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->scoringService->handleRaceCancellation($race, $request->string('reason'));

            return redirect()->back()->with('success', 'Race cancelled and predictions marked accordingly');
        } catch (\Throwable $e) {
            Log::error('Admin\RacesController@cancel failed', ['race_id' => $race->id, 'exception' => $e]);

            return redirect()->back()->with('error', 'Failed to cancel race. Please try again.');
        }
    }

    public function toggleHalfPoints(Request $request, Races $race): RedirectResponse
    {
        $this->authorize('update', $race);

        $race->update(['half_points' => ! $race->half_points]);

        $label = $race->half_points ? 'enabled' : 'disabled';

        return redirect()->back()->with('success', "Half-points {$label} for {$race->display_name}");
    }

    public function scoringStats(Races $race): JsonResponse
    {
        $this->authorize('view', $race);

        try {
            $stats = $this->scoringService->getRaceScoringStats($race);

            return response()->json($stats);
        } catch (\Throwable $e) {
            Log::error('Admin\RacesController@scoringStats failed', ['race_id' => $race->id, 'exception' => $e]);

            return response()->json(['error' => 'Unable to load scoring statistics. Please try again.'], 500);
        }
    }

    public function bulkScore(Request $request): RedirectResponse
    {
        $this->authorize('view-admin-dashboard');

        $request->validate([
            'race_ids' => 'required|array',
            'race_ids.*' => 'integer|exists:races,id',
        ]);

        $successCount = 0;
        $failureCount = 0;

        foreach ($request->input('race_ids') as $raceId) {
            try {
                $race = Races::find($raceId);
                $this->scoringService->scoreRacePredictions($race);
                $successCount++;
            } catch (\Throwable $e) {
                $failureCount++;
                Log::error('Admin\RacesController@bulkScore failed for race', ['race_id' => $raceId, 'exception' => $e]);
            }
        }

        $message = "Bulk scoring completed: {$successCount} successful, {$failureCount} failed";

        return redirect()->back()->with('success', $message);
    }
}
