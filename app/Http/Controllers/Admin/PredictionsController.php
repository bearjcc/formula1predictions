<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Services\ScoringService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PredictionsController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    public function __construct(
        private readonly ScoringService $scoringService,
    ) {}

    public function index(): View|RedirectResponse
    {
        try {
            $predictions = Prediction::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('admin.predictions', compact('predictions'));
        } catch (\Throwable $e) {
            Log::error('Admin\PredictionsController@index failed', ['exception' => $e]);

            return redirect()->route('admin.dashboard')->with('error', 'Unable to load predictions. Please try again.');
        }
    }

    public function score(Request $request, Prediction $prediction): RedirectResponse
    {
        $this->authorize('score', $prediction);

        if ($prediction->type !== 'race' || ! $prediction->race) {
            return redirect()->back()->with('error', 'Only race predictions with a linked race can be scored.');
        }

        $score = $this->scoringService->calculatePredictionScore($prediction, $prediction->race);

        $this->scoringService->savePredictionScore($prediction, $score);

        return redirect()->back()->with('success', "Prediction scored with {$score} points.");
    }

    public function lock(Prediction $prediction): RedirectResponse
    {
        $this->authorize('update', $prediction);

        $prediction->forceFill([
            'status' => 'locked',
            'locked_at' => now(),
        ])->save();

        return redirect()->back()->with('success', 'Prediction locked successfully.');
    }

    public function unlock(Prediction $prediction): RedirectResponse
    {
        $this->authorize('update', $prediction);

        $prediction->forceFill([
            'status' => 'draft',
            'locked_at' => null,
        ])->save();

        return redirect()->back()->with('success', 'Prediction unlocked successfully.');
    }

    public function destroy(Prediction $prediction): RedirectResponse
    {
        $this->authorize('manage-predictions');
        $this->authorize('delete', $prediction);

        $prediction->delete();

        return redirect()->back()->with('success', 'Prediction deleted successfully.');
    }

    public function overrideScore(Request $request, Prediction $prediction): RedirectResponse
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
        } catch (\Throwable $e) {
            Log::error('Admin\PredictionsController@overrideScore failed', ['prediction_id' => $prediction->id, 'exception' => $e]);

            return redirect()->back()->with('error', 'Failed to override score. Please try again.');
        }
    }
}
