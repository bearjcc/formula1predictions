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

        $minYear = 2022;
        $maxYear = max(2027, (int) config('f1.current_season', 2026));

        if (! is_numeric($year) || $year < $minYear || $year > $maxYear) {
            abort(404, "Invalid year specified. Please select a year between {$minYear} and {$maxYear}.");
        }

        return $next($request);
    }
}
