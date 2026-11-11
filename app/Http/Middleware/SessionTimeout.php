<?php

namespace App\Http\Middleware;

use App\Models\LoginHistory;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $timeout = config('session.inactivity_timeout', 30) * 60;
            $lastActivity = session('last_activity_time');

            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                // Log logout
                if ($sessionId = session('login_session_id')) {
                    LoginHistory::where('session_id', $sessionId)->update(['logout_at' => now()]);
                }
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session expired due to inactivity.'], 401);
                }
                return redirect()->route('login')->with('warning', 'Your session expired due to inactivity. Please login again.');
            }

            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}
