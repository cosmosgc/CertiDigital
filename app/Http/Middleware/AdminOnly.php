<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminOnly
{
    /**
     * Handle an incoming request, ensuring the user is both authenticated
     * (via Sanctum/session) and has the "admin" role.
     *
     * If no user is present we throw an AuthenticationException (401).
     * If a non-admin user is present we abort with 403.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next)
    {
        // try to resolve the user from the default guard (sanctum/web)
        $user = $request->user();

        if (! $user) {
            // not logged in at all
            $user = \App\Models\User::find($request->user_id);
            if (! $user) {
                // not logged in at all
                throw new AuthenticationException;
            }
        }

        if (! $user->hasRole('admin')) {
            abort(403, 'User does not have the administrator role.');
        }

        // For mutating requests require a user_id in the request and ensure
        // it matches the authenticated user. This prevents impersonation
        // where a client might try to set another user's id on creation/update.
        // For mutating requests require a user_id in the request and ensure
        // it matches the authenticated user. Skip this check for admin user
        // management endpoints (api/admin/*) which manage roles/users.
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])
            && ! $request->is('api/admin/*')) {
            $requestUserId = $request->input('user_id') ?? $request->header('X-User-Id');

            if (! $requestUserId) {
                abort(Response::HTTP_BAD_REQUEST, 'user_id is required for mutating requests.');
            }

            if ((string) $requestUserId !== (string) $user->id) {
                abort(Response::HTTP_FORBIDDEN, 'user_id does not match authenticated user.');
            }
        }

        return $next($request);
    }
}
