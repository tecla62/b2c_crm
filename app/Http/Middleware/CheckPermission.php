<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. You do not have permission to access this resource.'], 403);
            }
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
