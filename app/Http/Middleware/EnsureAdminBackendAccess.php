<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminBackendAccess
{
    /**
     * Handle an incoming request.
     *
     * Restricts access exclusively to:
     * - Super Admin
     * - Admin
     * - Staff
     *
     * Agents and End Users are strictly forbidden (HTTP 403).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->hasAdminBackendAccess()) {
            abort(403, 'Unauthorized. Administrative backend access is restricted to authorized personnel only.');
        }

        return $next($request);
    }
}
