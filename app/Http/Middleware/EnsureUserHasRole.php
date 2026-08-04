<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admins have access to all routes
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Managers have access to all routes EXCEPT those explicitly requiring admin
        if ($user->isManager() && ! in_array('admin', $roles, true)) {
            return $next($request);
        }

        // Check if user's role is in the allowed roles list
        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access to this module.');
    }
}
