<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePredictionRequest;
use App\Http\Requests\UpdatePredictionRequest;
use App\Models\Prediction;
use Illuminate\Http\RedirectResponse;
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

        return view('predictions.index', compact('predictions'));
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
    public function show(Prediction $prediction): View
    {
        Gate::authorize('view', $prediction);

        return view('predictions.show', compact('prediction'));
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
