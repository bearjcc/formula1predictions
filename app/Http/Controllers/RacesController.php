<?php

namespace App\Http\Controllers;

use App\Services\F1ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RacesController extends Controller
{
    public function __construct(
        private F1ApiService $f1ApiService
    ) {}

    /**
     * Display a listing of the resource (API).
     */
    public function index(Request $request, int $year)
    {
        try {
            $races = $this->f1ApiService->getRacesForYear($year);

            return response()->json($races);
        } catch (\Throwable $e) {
            Log::error('RacesController@index failed', ['year' => $year, 'exception' => $e]);

            return response()->json(['error' => 'Unable to load race calendar. Please try again later.'], 500);
        }
    }

    /**
     * Display the specified resource (API).
     */
    public function show(Request $request, int $year, int $round)
    {
        try {
            $race = $this->f1ApiService->getRaceResults($year, $round);

            return response()->json($race);
        } catch (\Throwable $e) {
            Log::error('RacesController@show failed', ['year' => $year, 'round' => $round, 'exception' => $e]);

            return response()->json(['error' => 'Unable to load race results. Please try again later.'], 500);
        }
    }

    /**
     * Test the F1 API connection
     */
    public function testApi()
    {
        try {
            $isConnected = $this->f1ApiService->testConnection();

            return response()->json(['connected' => $isConnected]);
        } catch (\Throwable $e) {
            Log::error('RacesController@testApi failed', ['exception' => $e]);

            return response()->json(['connected' => false, 'error' => 'Unable to verify connection. Please try again.']);
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
        } catch (\Throwable $e) {
            Log::error('RacesController@clearCache failed', ['year' => $year, 'exception' => $e]);

            return response()->json(['error' => 'Unable to clear cache. Please try again later.'], 500);
        }
    }
}
