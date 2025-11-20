<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check approval for authenticated users
        if (! $request->user()) {
            return $next($request);
        }

        // Admins are always approved
        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        // Check if student is approved
        if (! $request->user()->isApproved()) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('home')
                ->with('error', 'Your account is pending approval. Please wait for admin approval.');
        }

        return $next($request);
    }
}
