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
        // TODO: Replace hardcoded year validation with database lookup
        // TODO: Add support for future years (2024, 2025, etc.)
        // TODO: Implement proper error messages for invalid years
        // TODO: Add logging for invalid year attempts
        // TODO: Consider caching valid years for performance
        // TODO: Add unit tests for year validation logic
        
        $year = $request->route('year');
        
        // FIXME: This is temporary validation - replace with database check
        if ($year !== '2022' && $year !== '2023') {
            abort(404, 'Invalid year specified.');
        }

        return $next($request);
    }
}
