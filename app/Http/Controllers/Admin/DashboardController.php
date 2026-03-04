<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        try {
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
        } catch (\Throwable $e) {
            Log::error('Admin\DashboardController@index failed', ['exception' => $e]);

            return redirect()->back()->with('error', 'Unable to load dashboard. Please try again.');
        }
    }
}
