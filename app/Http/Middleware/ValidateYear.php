<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateYear
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $year = $request->route('year');

        // Validate year format and range - only allow 2022-2024 for now
        if (!is_numeric($year) || $year < 2022 || $year > 2024) {
            abort(404, 'Invalid year specified. Please select a year between 2022 and 2024.');
        }

        // TODO: Add database lookup for available years
        // TODO: Add caching for valid years
        // TODO: Add unit tests for year validation logic

        return $next($request);
    }
}
