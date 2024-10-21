<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
         // Check if the user is authenticated and has the "Super Admin" role
         if (Auth::check() && Auth::user()->hasRole('Super Admin')) {
            return $next($request);
        }

        // Return a custom JSON response for unauthorized access
        return response()->json([
            'status' => '403',
            'message' => 'Permission denied, Unauthorized Access',
            'additional_message' => 'You do not have the right role to access this route.',
        ], 403);
    }
}
