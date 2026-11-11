@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="auth-header">
    <div class="logo"><i class="bi bi-shield-lock"></i></div>
    <h2>Reset Password</h2>
    <p>Enter your new password</p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ $errors->first() }}
    </div>
@endif

<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <div class="mb-3">
        <label class="form-label">New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Enter new password" required autofocus>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-circle me-2"></i> Reset Password
    </button>
</form>

<div class="auth-footer">
    <p>Remember your password? <a href="{{ route('login') }}">Login here</a></p>
</div>
@endsection
