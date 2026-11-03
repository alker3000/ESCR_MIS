<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request and enforce Role-Based Access Control (RBAC).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please sign in to access this page.');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated by the Administrator.');
        }

        if (in_array($user->role, $roles, true) || $user->role === 'super_admin') {
            return $next($request);
        }

        return redirect()
            ->route($user->dashboardRoute())
            ->with('error', 'Access Denied: Your role (' . $user->roleLabel() . ') does not have permission to view that section.');
    }
}
