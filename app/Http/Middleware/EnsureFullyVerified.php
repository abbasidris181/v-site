<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFullyVerified
{
    /**
     * Handle an incoming request.
     *
     * Enforces that both email and phone verifications are completed
     * before accessing the protected portal services and dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Allow verification routes to pass through to avoid redirect loops
        if ($request->routeIs('verification.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Check Email verification
        if (! $user->isEmailVerified()) {
            return redirect()->route('verification.email.notice');
        }

        // Check Phone verification
        if (! $user->isPhoneVerified()) {
            return redirect()->route('verification.phone.notice');
        }

        return $next($request);
    }
}
