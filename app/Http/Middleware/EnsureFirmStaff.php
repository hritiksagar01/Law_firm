<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFirmStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isClient()) {
            return redirect()->route('portal.dashboard')
                ->with('info', 'Client accounts access their case files via the Client Portal.');
        }

        return $next($request);
    }
}
