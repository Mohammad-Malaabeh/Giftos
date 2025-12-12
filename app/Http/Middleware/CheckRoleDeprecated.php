<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated Use `\App\Http\Middleware\RoleMiddleware` and the `role` middleware alias instead.
 *             `CheckRole` will be removed in a future release.
 */
class CheckRoleDeprecated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if ($role === null) {
            return $next($request);
        }

        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        $user = $request->user();
        /** @var \App\Models\User $user */
        // Check using the same method as the request authorization
        if ($role === 'admin' && !$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Insufficient permissions.'], 403);
            }

            abort(403);
        }

        // Fallback to direct role check for other roles
        if ($role !== 'admin' && $user->role !== $role) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Insufficient permissions.'], 403);
            }

            abort(403);
        }

        return $next($request);
    }
}
