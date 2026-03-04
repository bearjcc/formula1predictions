<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    use AuthorizesRequests;

    public function index(): View|RedirectResponse
    {
        $this->authorize('viewAny', Feedback::class);

        try {
            $feedback = Feedback::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('admin.feedback', compact('feedback'));
        } catch (\Throwable $e) {
            Log::error('Admin\FeedbackController@index failed', ['exception' => $e]);

            return redirect()->route('admin.dashboard')->with('error', 'Unable to load feedback. Please try again.');
        }
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $this->authorize('delete', $feedback);

        $feedback->delete();

        return redirect()->back()->with('success', 'Feedback deleted.');
    }
}
