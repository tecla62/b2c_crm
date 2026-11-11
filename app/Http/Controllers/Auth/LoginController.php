<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function showLoginForm()
    {
        if (Auth::check()) return redirect()->route('admin.dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->attemptLogin($request);
            $request->session()->regenerate();
            return redirect()->intended($result['redirect'])
                ->with('success', 'Welcome back, ' . $result['user']->name . '!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput($request->only('email'));
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
