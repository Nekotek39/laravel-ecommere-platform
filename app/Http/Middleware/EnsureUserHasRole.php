<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows the request only for users with one of the given roles.
 *
 * Usage in routes: ->middleware('role:admin') or ->middleware('role:admin,moderator')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role->value, $roles, true), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
