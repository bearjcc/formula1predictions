<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View|RedirectResponse
    {
        try {
            return view('admin.settings');
        } catch (\Throwable $e) {
            Log::error('Admin\SettingsController@index failed', ['exception' => $e]);

            return redirect()->route('admin.dashboard')->with('error', 'Unable to load settings. Please try again.');
        }
    }
}
