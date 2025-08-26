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
        // Testing Scaffolding
        // TODO: Implement proper year validation
        $year = $request->route('year');
        if ($year !== '2022' && $year !== '2023') {
            abort(404);
        }

        return $next($request);
    }
}
