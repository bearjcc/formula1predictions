<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePredictionRequest;
use App\Http\Requests\UpdatePredictionRequest;
use App\Models\Prediction;
use App\Models\Races;
use App\Services\ScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PredictionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $predictions = Auth::user()->predictions()
            ->with('race')
            ->latest()
            ->paginate(10);

        $nextRace = Races::nextAvailableForPredictions();

        return view('predictions.index', compact('predictions', 'nextRace'));
    }

    /**
     * Show the Livewire form for creating a race/sprint prediction (with optional race_id).
     */
    public function createForm(Request $request): View|RedirectResponse
    {
        $race = null;

        if ($request->filled('race_id')) {
            $raceId = (int) $request->input('race_id');
            $race = Races::find($raceId);
        } else {
            $nextRace = Races::nextAvailableForPredictions();
            if ($nextRace !== null) {
                return to_route('predict.create', ['race_id' => $nextRace->id]);
            }
        }

        if ($race !== null) {
            $existing = $request->user()->predictions()
                ->where('season', $race->season)
                ->where('race_round', $race->round)
                ->whereIn('type', ['race', 'sprint'])
                ->first();
            if ($existing !== null) {
                return to_route('predictions.edit', $existing)
                    ->with('info', 'You already have a prediction for this race.');
            }
        }

        return view('predictions.create-livewire', compact('race'));
    }

    /**
     * Show the Livewire form for creating a preseason prediction.
     */
    public function preseasonForm(Request $request): View
    {
        $year = (int) $request->input('year', config('f1.current_season'));

        return view('predictions.create-livewire', [
            'race' => null,
            'preseason' => true,
            'year' => $year,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Redirects to Livewire prediction form for consistency.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('predict.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePredictionRequest $request): RedirectResponse
    {
        $prediction = Auth::user()->predictions()->create($request->validated());

        return redirect()->route('predictions.show', $prediction)
            ->with('success', 'Prediction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prediction $prediction, ScoringService $scoringService): View
    {
        Gate::authorize('view', $prediction);

        $breakdown = null;
        if ($prediction->status === 'scored' && in_array($prediction->type, ['race', 'sprint'], true) && $prediction->race) {
            $breakdown = $scoringService->getPredictionBreakdown($prediction, $prediction->race);
        }

        return view('predictions.show', compact('prediction', 'breakdown'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prediction $prediction): View
    {
        Gate::authorize('update', $prediction);

        return view('predictions.edit-livewire', compact('prediction'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePredictionRequest $request, Prediction $prediction): RedirectResponse
    {
        Gate::authorize('update', $prediction);

        $prediction->update($request->validated());

        return redirect()->route('predictions.show', $prediction)
            ->with('success', 'Prediction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prediction $prediction): RedirectResponse
    {
        Gate::authorize('delete', $prediction);

        $prediction->delete();

        return redirect()->route('predictions.index')
            ->with('success', 'Prediction deleted successfully.');
    }
}
