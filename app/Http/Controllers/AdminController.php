<?php

namespace App\Http\Controllers;

use App\Jobs\ScoreRacePredictionsJob;
use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Teams;
use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ScoringService $scoringService
    ) {}

    /**
     * Show the admin dashboard.
     */
    public function dashboard(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_predictions' => Prediction::count(),
            'total_races' => Races::count(),
            'total_drivers' => Drivers::count(),
            'total_teams' => Teams::count(),
            'recent_predictions' => Prediction::with('user')->latest()->take(10)->get(),
            'pending_predictions' => Prediction::where('status', 'submitted')->count(),
            'scored_predictions' => Prediction::where('status', 'scored')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Show user management page.
     */
    public function users(): View
    {
        $users = User::withCount('predictions')
            ->withSum('predictions', 'score')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Show prediction management page.
     */
    public function predictions(): View
    {
        $predictions = Prediction::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.predictions', compact('predictions'));
    }

    /**
     * Show race management page.
     */
    public function races(): View
    {
        $races = Races::orderBy('date', 'desc')
            ->paginate(20);

        return view('admin.races', compact('races'));
    }

    /**
     * Score a prediction.
     */
    public function scorePrediction(Request $request, Prediction $prediction): RedirectResponse
    {
        $this->authorize('score', $prediction);

        if ($prediction->type !== 'race' || ! $prediction->race) {
            return redirect()->back()->with('error', 'Only race predictions with a linked race can be scored.');
        }

        $score = $this->scoringService->calculatePredictionScore($prediction, $prediction->race);

        $this->scoringService->savePredictionScore($prediction, $score);

        return redirect()->back()->with('success', "Prediction scored with {$score} points.");
    }

    /**
     * Lock a prediction (prevent further edits).
     */
    public function lockPrediction(Prediction $prediction): RedirectResponse
    {
        $this->authorize('update', $prediction);

        $prediction->update([
            'status' => 'locked',
            'locked_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Prediction locked successfully.');
    }

    /**
     * Unlock a prediction (allow edits).
     */
    public function unlockPrediction(Prediction $prediction): RedirectResponse
    {
        $this->authorize('update', $prediction);

        $prediction->update([
            'status' => 'draft',
            'locked_at' => null,
        ]);

        return redirect()->back()->with('success', 'Prediction unlocked successfully.');
    }

    /**
     * Delete a prediction.
     */
    public function deletePrediction(Prediction $prediction): RedirectResponse
    {
        $this->authorize('delete', $prediction);

        $prediction->delete();

        return redirect()->back()->with('success', 'Prediction deleted successfully.');
    }

    /**
     * Show system settings page.
     */
    public function settings(): View
    {
        return view('admin.settings');
    }

    /**
     * Show scoring management page.
     */
    public function scoring(): View
    {
        $races = Races::withCount(['predictions' => function ($query) {
            $query->whereIn('status', ['submitted', 'locked']);
        }])
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('admin.scoring', compact('races'));
    }

    /**
     * Automatically score predictions for a race.
     */
    public function scoreRace(Request $request, Races $race): RedirectResponse
    {
        $this->authorize('manageResults', $race);

        try {
            $results = $this->scoringService->scoreRacePredictions($race);

            $message = "Successfully scored {$results['scored_predictions']} predictions for {$results['total_score']} total points.";

            if ($results['failed_predictions'] > 0) {
                $message .= " {$results['failed_predictions']} predictions failed to score.";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Failed to score race: {$e->getMessage()}");
        }
    }

    /**
     * Queue background scoring for a race.
     */
    public function queueRaceScoring(Request $request, Races $race): RedirectResponse
    {
        $this->authorize('manageResults', $race);

        try {
            ScoreRacePredictionsJob::dispatch($race->id, $request->boolean('force_update'));

            return redirect()->back()->with('success', "Scoring job queued for {$race->race_name}");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Failed to queue scoring: {$e->getMessage()}");
        }
    }

    /**
     * Override prediction score manually.
     */
    public function overridePredictionScore(Request $request, Prediction $prediction): RedirectResponse
    {
        $this->authorize('score', $prediction);

        $request->validate([
            'score' => 'required|integer|min:-100|max:500',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->scoringService->overridePredictionScore(
                $prediction,
                $request->integer('score'),
                $request->string('reason')
            );

            return redirect()->back()->with('success', "Prediction score overridden to {$request->score} points");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Failed to override score: {$e->getMessage()}");
        }
    }

    /**
     * Handle driver substitutions for a race.
     */
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

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Failed to apply substitutions: {$e->getMessage()}");
        }
    }

    /**
     * Handle race cancellation.
     */
    public function handleRaceCancellation(Request $request, Races $race): RedirectResponse
    {
        $this->authorize('manageResults', $race);

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->scoringService->handleRaceCancellation($race, $request->string('reason'));

            return redirect()->back()->with('success', 'Race cancelled and predictions marked accordingly');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Failed to cancel race: {$e->getMessage()}");
        }
    }

    /**
     * Get scoring statistics for a race.
     */
    public function getRaceScoringStats(Races $race): JsonResponse
    {
        $this->authorize('view', $race);

        try {
            $stats = $this->scoringService->getRaceScoringStats($race);

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk score multiple races.
     */
    public function bulkScoreRaces(Request $request): RedirectResponse
    {
        $this->authorize('manageResults', Races::class);

        $request->validate([
            'race_ids' => 'required|array',
            'race_ids.*' => 'integer|exists:races,id',
        ]);

        $successCount = 0;
        $failureCount = 0;

        foreach ($request->input('race_ids') as $raceId) {
            try {
                $race = Races::find($raceId);
                $results = $this->scoringService->scoreRacePredictions($race);
                $successCount++;
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Bulk scoring failed for race {$raceId}: {$e->getMessage()}");
            }
        }

        $message = "Bulk scoring completed: {$successCount} successful, {$failureCount} failed";

        return redirect()->back()->with('success', $message);
    }
}
