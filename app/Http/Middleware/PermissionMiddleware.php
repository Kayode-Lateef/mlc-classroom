<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions  Multiple permissions can be passed
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please log in to access this page.');
        }

        $user = auth()->user();

        // SuperAdmin has all permissions
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        // User doesn't have required permission
        abort(403, 'Unauthorized access. You do not have the required permissions.');
    }
}