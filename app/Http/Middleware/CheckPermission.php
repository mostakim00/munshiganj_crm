<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        if (!Auth::user()->hasPermissionTo($permission)) {
            return response()->json([
                'status'=>'403',
                'message' => 'Permission denied,Unauthorized Access',
                'additional_message' => 'You do not have the right permission to access this route.',
                
            ], 403);
        }

        return $next($request);
    }
}
