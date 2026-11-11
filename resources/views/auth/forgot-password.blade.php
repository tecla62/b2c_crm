@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-header">
    <div class="logo"><i class="bi bi-key"></i></div>
    <h2>Forgot Password?</h2>
    <p>Enter your email to reset your password</p>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ $errors->first() }}
    </div>
@endif

<form action="{{ route('password.email') }}" method="POST">
    @csrf
    <div class="mb-4">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-send me-2"></i> Send Reset Link
    </button>
</form>

<div class="auth-footer">
    <p>Remember your password? <a href="{{ route('login') }}">Login here</a></p>
</div>
@endsection
