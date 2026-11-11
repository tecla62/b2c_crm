{{-- ── Login Page Settings ──────────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-box-arrow-in-right me-2 text-primary"></i>Login Page Settings</div>
<p class="settings-section-sub">Customize the login page appearance and content.</p>

<form id="form-login" action="{{ route('admin.settings.save', 'login') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-image"></i><h6>Login Page Branding</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                {{-- Login Logo --}}
                <div class="col-md-6">
                    <label class="settings-label">Login Logo</label>
                    <div class="img-upload-wrap">
                        <input type="file" name="login_logo" class="img-file-input" data-preview="prev-login_logo" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-cloud-upload"></i></div>
                        <div class="upload-text">Click or drag to upload</div>
                        <div class="upload-hint">PNG, SVG — max 2MB</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-login_logo" style="{{ ($settings['login_logo'] ?? '') ? '' : 'display:none' }}">
                        <img src="{{ setting_image('login_logo') }}" alt="Login Logo">
                        <button type="button" class="btn-remove-img" data-key="login_logo" data-preview="prev-login_logo"><i class="bi bi-x"></i></button>
                    </div>
                </div>

                {{-- Background Image --}}
                <div class="col-md-6">
                    <label class="settings-label">Login Background Image</label>
                    <div class="img-upload-wrap">
                        <input type="file" name="login_bg_image" class="img-file-input" data-preview="prev-login_bg_image" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-cloud-upload"></i></div>
                        <div class="upload-text">Click or drag to upload</div>
                        <div class="upload-hint">JPG, PNG — 1920×1080 recommended</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-login_bg_image" style="{{ ($settings['login_bg_image'] ?? '') ? '' : 'display:none' }}">
                        <img src="{{ setting_image('login_bg_image') }}" alt="Login Background">
                        <button type="button" class="btn-remove-img" data-key="login_bg_image" data-preview="prev-login_bg_image"><i class="bi bi-x"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-type"></i><h6>Login Page Content</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">Welcome Text</label>
                    <input type="text" name="login_welcome_text" class="settings-input" value="{{ $settings['login_welcome_text'] ?? 'Welcome Back!' }}" placeholder="Welcome Back!">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Login Page Title</label>
                    <input type="text" name="login_page_title" class="settings-input" value="{{ $settings['login_page_title'] ?? 'Sign in to your account' }}" placeholder="Sign in to your account">
                </div>
                <div class="col-12">
                    <label class="settings-label">Footer Text</label>
                    <input type="text" name="login_footer_text" class="settings-input" value="{{ $settings['login_footer_text'] ?? '' }}" placeholder="© 2025 WaterProof CRM. All rights reserved.">
                </div>
            </div>
        </div>
    </div>

    <div class="settings-save-bar">
        <span class="save-status" id="save-status-login"></span>
        <button type="button" id="btn-save-login" class="btn btn-primary px-4" onclick="saveSettings('login')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>
