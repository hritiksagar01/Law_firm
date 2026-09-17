<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request and verify the user has the required permission.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => "Unauthorized: You do not possess the required '{$permission}' permission.",
            ], 403);
        }

        abort(403, "Access Denied: You do not possess the '{$permission}' permission for this legal module.");
    }
}
