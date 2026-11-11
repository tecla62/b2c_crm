{{-- ── Security Settings ────────────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-shield-lock me-2 text-primary"></i>Security Settings</div>
<p class="settings-section-sub">Configure session, password policies, login protection and 2FA.</p>

<form id="form-security" action="{{ route('admin.settings.save', 'security') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Session & Login --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-clock-history"></i><h6>Session & Login</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">Session Timeout (minutes)</label>
                    <input type="number" name="session_timeout" class="settings-input" value="{{ $settings['session_timeout'] ?? '120' }}" min="5">
                    <span class="settings-hint">Auto logout after inactivity</span>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Login Attempt Limit</label>
                    <input type="number" name="login_attempt_limit" class="settings-input" value="{{ $settings['login_attempt_limit'] ?? '5' }}" min="1" max="20">
                    <span class="settings-hint">Lock account after N failed attempts</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Password Policy --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-key"></i><h6>Password Policy</h6></div>
        <div class="settings-card-body">
            <div class="row g-4 mb-3">
                <div class="col-md-4">
                    <label class="settings-label">Minimum Password Length</label>
                    <input type="number" name="password_min_length" class="settings-input" value="{{ $settings['password_min_length'] ?? '8' }}" min="6" max="32">
                </div>
            </div>
            @php
            $pwdToggles = [
                'password_require_upper'  => ['Require Uppercase Letter', 'Password must contain at least one uppercase letter'],
                'password_require_number' => ['Require Number', 'Password must contain at least one number'],
                'password_require_symbol' => ['Require Special Symbol', 'Password must contain at least one special character'],
            ];
            @endphp
            @foreach($pwdToggles as $key => [$label, $desc])
            <div class="toggle-wrap">
                <div class="toggle-info">
                    <div class="toggle-label">{{ $label }}</div>
                    <div class="toggle-desc">{{ $desc }}</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" {{ ($settings[$key] ?? '0') == '1' ? 'checked' : '' }}>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- reCAPTCHA --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-robot"></i><h6>Google reCAPTCHA</h6></div>
        <div class="settings-card-body">
            <div class="toggle-wrap mb-3">
                <div class="toggle-info">
                    <div class="toggle-label">Enable reCAPTCHA on Login</div>
                    <div class="toggle-desc">Protect login form with Google reCAPTCHA v2</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="recaptcha_enabled" value="1" {{ ($settings['recaptcha_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">reCAPTCHA Site Key</label>
                    <input type="text" name="recaptcha_site_key" class="settings-input" value="{{ $settings['recaptcha_site_key'] ?? '' }}" placeholder="6Le…">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">reCAPTCHA Secret Key</label>
                    <input type="password" name="recaptcha_secret_key" class="settings-input" value="{{ $settings['recaptcha_secret_key'] ?? '' }}" placeholder="6Le…">
                </div>
            </div>
        </div>
    </div>

    {{-- 2FA --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-phone-vibrate"></i><h6>Two-Factor Authentication</h6></div>
        <div class="settings-card-body">
            <div class="toggle-wrap">
                <div class="toggle-info">
                    <div class="toggle-label">Enable 2FA by Default</div>
                    <div class="toggle-desc">Require two-factor authentication for all users</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="two_factor_default" value="1" {{ ($settings['two_factor_default'] ?? '0') == '1' ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-save-bar">
        <span class="save-status" id="save-status-security"></span>
        <button type="button" id="btn-save-security" class="btn btn-primary px-4" onclick="saveSettings('security')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>
