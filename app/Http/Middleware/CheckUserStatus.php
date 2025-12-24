<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If no user, let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check user status
        if ($user->status === 'suspended') {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been suspended. Please contact an administrator.');
        }

        if ($user->status === 'inactive') {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account is inactive. Please contact an administrator.');
        }

        if ($user->status === 'banned') {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been permanently banned.');
        }

        return $next($request);
    }
}