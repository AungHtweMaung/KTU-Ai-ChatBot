<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to admin users only.
 *
 * Assumes the route is already behind the `auth` middleware. A logged-in
 * non-admin is bounced to the chat UI (their home); a guest is sent to login.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isAdmin()) {
            return redirect()->route('chat');
        }

        return $next($request);
    }
}
