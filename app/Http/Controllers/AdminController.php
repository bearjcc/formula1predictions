<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Drivers;
use App\Models\Teams;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class AdminController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

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

        $score = $prediction->calculateScore();
        $prediction->update([
            'score' => $score,
            'status' => 'scored',
            'scored_at' => now(),
        ]);

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
}
