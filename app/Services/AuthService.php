<?php

namespace App\Services;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function attemptLogin(Request $request): array
    {
        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');
        $ip          = $request->ip();

        // Find user by email or mobile
        $user = User::where('email', $credentials['email'])
            ->orWhere('mobile', $credentials['email'])
            ->first();

        if (!$user) {
            $this->logFailedAttempt(null, $ip, $request->userAgent(), 'User not found');
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            $this->logFailedAttempt($user, $ip, $request->userAgent(), 'Account locked');
            $minutes = now()->diffInMinutes($user->locked_until);
            throw ValidationException::withMessages(['email' => "Account is locked. Try again in {$minutes} minutes."]);
        }

        // Check if account is active
        if (!$user->isActive()) {
            $this->logFailedAttempt($user, $ip, $request->userAgent(), 'Account inactive/suspended');
            throw ValidationException::withMessages(['email' => 'Your account has been ' . $user->status . '. Please contact administrator.']);
        }

        // Verify password
        if (!Hash::check($credentials['password'], $user->password)) {
            $this->handleFailedAttempt($user, $ip, $request->userAgent());
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        // Reset failed attempts on success
        $user->update(['failed_login_attempts' => 0, 'locked_until' => null]);

        Auth::login($user, $remember);

        $sessionId = session()->getId();
        session(['last_activity_time' => time(), 'login_session_id' => $sessionId]);

        // Log successful login
        LoginHistory::create([
            'user_id'    => $user->id,
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'browser'    => $this->getBrowser($request->userAgent()),
            'device'     => $this->getDevice($request->userAgent()),
            'platform'   => $this->getPlatform($request->userAgent()),
            'status'     => 'success',
            'login_at'   => now(),
            'session_id' => $sessionId,
        ]);

        $user->update(['last_login_at' => now(), 'last_login_ip' => $ip]);

        return ['user' => $user, 'redirect' => $this->getRedirectRoute($user)];
    }

    public function logout(Request $request): void
    {
        if ($sessionId = session('login_session_id')) {
            LoginHistory::where('session_id', $sessionId)->update(['logout_at' => now()]);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function handleFailedAttempt(User $user, string $ip, ?string $userAgent): void
    {
        $maxAttempts = config('auth.max_attempts', 5);
        $lockoutMinutes = config('auth.lockout_minutes', 15);

        $attempts = $user->failed_login_attempts + 1;
        $data = ['failed_login_attempts' => $attempts];

        if ($attempts >= $maxAttempts) {
            $data['locked_until'] = now()->addMinutes($lockoutMinutes);
            $this->logFailedAttempt($user, $ip, $userAgent, 'Max attempts reached - account locked');
        } else {
            $this->logFailedAttempt($user, $ip, $userAgent, 'Invalid password');
        }

        $user->update($data);
    }

    private function logFailedAttempt(?User $user, string $ip, ?string $userAgent, string $reason): void
    {
        if ($user) {
            LoginHistory::create([
                'user_id'        => $user->id,
                'ip_address'     => $ip,
                'user_agent'     => $userAgent,
                'browser'        => $this->getBrowser($userAgent),
                'device'         => $this->getDevice($userAgent),
                'platform'       => $this->getPlatform($userAgent),
                'status'         => 'failed',
                'failure_reason' => $reason,
                'login_at'       => now(),
            ]);
        }
    }

    private function getRedirectRoute(User $user): string
    {
        return route('admin.dashboard');
    }

    private function getBrowser(?string $ua): string
    {
        if (!$ua) return 'Unknown';
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari')) return 'Safari';
        if (str_contains($ua, 'Edge')) return 'Edge';
        if (str_contains($ua, 'Opera')) return 'Opera';
        return 'Unknown';
    }

    private function getDevice(?string $ua): string
    {
        if (!$ua) return 'Unknown';
        if (str_contains($ua, 'Mobile') || str_contains($ua, 'Android')) return 'Mobile';
        if (str_contains($ua, 'Tablet') || str_contains($ua, 'iPad')) return 'Tablet';
        return 'Desktop';
    }

    private function getPlatform(?string $ua): string
    {
        if (!$ua) return 'Unknown';
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac')) return 'macOS';
        if (str_contains($ua, 'Linux')) return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iOS') || str_contains($ua, 'iPhone')) return 'iOS';
        return 'Unknown';
    }
}
