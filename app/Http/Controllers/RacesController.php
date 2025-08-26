<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRacesRequest;
use App\Http\Requests\UpdateRacesRequest;
use App\Models\Races;
use App\Services\F1ApiService;
use Illuminate\Http\Request;

class RacesController extends Controller
{
    public function __construct(
        private F1ApiService $f1ApiService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));

        try {
            $races = $this->f1ApiService->getRacesForYear((int) $year);

            return response()->json($races);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRacesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $year, int $round)
    {
        try {
            $race = $this->f1ApiService->getRaceResults($year, $round);

            return response()->json($race);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Races $races)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRacesRequest $request, Races $races)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Races $races)
    {
        //
    }

    /**
     * Test the F1 API connection
     */
    public function testApi()
    {
        try {
            $isConnected = $this->f1ApiService->testConnection();

            return response()->json(['connected' => $isConnected]);
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Clear cache for a specific year
     */
    public function clearCache(int $year)
    {
        try {
            $this->f1ApiService->clearCache($year);

            return response()->json(['message' => "Cache cleared for year {$year}"]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
