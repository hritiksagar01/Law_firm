<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->isClient()) {
            return redirect()->route('dashboard')
                ->with('info', 'Advocates and staff manage cases from the Chambers workspace.');
        }

        return $next($request);
    }
}
