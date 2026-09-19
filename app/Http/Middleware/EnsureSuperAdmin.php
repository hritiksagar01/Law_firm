<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     * Enforce strict authentication: User must be actively logged in with role 'superadmin'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized: Super Administrator authentication required.'], 403);
        }

        return redirect()->route('login')
            ->with('error', 'Super Administrator authentication required. Please sign in with your credentials.');
    }
}
