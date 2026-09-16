<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If session already verified this client as Super Admin, grant access without querying DB
        if ($request->session()->get('is_super_admin') === true) {
            return $next($request);
        }

        try {
            if (Auth::check() && Auth::user()->isSuperAdmin()) {
                $request->session()->put('is_super_admin', true);
                $request->session()->put('super_admin_email', Auth::user()->email);
                return $next($request);
            }
        } catch (\Throwable $e) {
            // Database is unreachable/offline, but if session was previously authenticated, allow management
            if ($request->session()->get('is_super_admin') === true) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized: Super Administrator access required.'], 403);
        }

        return redirect()->route('login')
            ->with('error', 'Super Administrator access required.');
    }
}
