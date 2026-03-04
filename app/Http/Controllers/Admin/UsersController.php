<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UsersController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    public function index(): View|RedirectResponse
    {
        try {
            $users = User::withCount('predictions')
                ->withSum('predictions', 'score')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('admin.users', compact('users'));
        } catch (\Throwable $e) {
            Log::error('Admin\UsersController@index failed', ['exception' => $e]);

            return redirect()->route('admin.dashboard')->with('error', 'Unable to load users. Please try again.');
        }
    }

    public function promote(User $user): RedirectResponse
    {
        $this->authorize('manageRoles', $user);

        if ($user->is_admin) {
            return redirect()->back()->with('error', 'User is already an admin.');
        }

        $user->forceFill(['is_admin' => true])->save();

        return redirect()->back()->with('success', "{$user->name} has been promoted to admin.");
    }

    public function demote(User $user): RedirectResponse
    {
        $this->authorize('manageRoles', $user);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot demote yourself.');
        }

        if (! $user->is_admin) {
            return redirect()->back()->with('error', 'User is not an admin.');
        }

        $user->forceFill(['is_admin' => false])->save();

        return redirect()->back()->with('success', "{$user->name} has been demoted from admin.");
    }
}
