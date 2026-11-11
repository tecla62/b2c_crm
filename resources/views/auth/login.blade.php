@extends('layouts.auth')

@section('title', setting('login_page_title', 'Login'))

@push('styles')
<style>
.login-kicker{
    display:inline-flex;align-items:center;gap:7px;
    background:#f8fafc;border:1px solid #e2e8f0;color:#475569;
    border-radius:999px;padding:6px 11px;font-size:12px;font-weight:800;
    margin-bottom:18px;
}
.login-form .input-group{box-shadow:0 1px 2px rgba(15,23,42,.04);}
.login-form .input-group-text{min-width:44px;justify-content:center;color:#64748b;}
.login-form .form-control{min-height:46px;}
.login-form .form-control::placeholder{color:#94a3b8;}
.login-options{gap:12px;}
.login-support{
    margin-top:18px;padding:13px 14px;border-radius:12px;
    background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;
    font-size:12.5px;line-height:1.55;text-align:center;
}
.login-support strong{color:#0f172a;}
@media(max-width:480px){
    .login-kicker{font-size:11.5px;padding:5px 10px;margin-bottom:14px;}
    .login-form .mb-4{margin-bottom:16px!important;}
    .login-options{align-items:flex-start!important;flex-direction:column;}
    .login-options .auth-link{align-self:flex-end;}
    .login-support{margin-top:14px;padding:11px 12px;font-size:11.8px;}
}
</style>
@endpush

@section('content')

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
    </div>
@endif



<form action="{{ route('login.post') }}" method="POST" class="login-form">
    @csrf

    <div class="mb-4">
        <label class="form-label">Email / Mobile</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror"
                   placeholder="Enter email or mobile"
                   value="{{ old('email') }}" required autofocus>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="passwordInput" class="form-control has-trailing-action" placeholder="Enter password" required>
            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()" title="Show or hide password">
                <i class="bi bi-eye" id="passwordToggleIcon"></i>
            </button>
        </div>
    </div>

    <div class="login-options d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember" style="font-size:13px;">Remember me</label>
        </div>
        <a href="{{ route('password.request') }}" class="auth-link" style="font-size:13px;">Forgot Password?</a>
    </div>

    <button type="submit" class="btn-auth">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
    </button>

    @if(setting('company_email') || setting('company_mobile'))
    <div class="login-support">
        <strong>Need access?</strong>
        Contact admin
        @if(setting('company_mobile')) at {{ setting('company_mobile') }}@endif
    </div>
    @endif
</form>

@endsection

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('passwordToggleIcon');
    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    icon.className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
@endpush
