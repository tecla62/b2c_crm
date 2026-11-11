@extends('layouts.app')
@section('title', 'Create User')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Create New User</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<form id="userForm" method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
@csrf
<div class="row g-4">

    {{-- ── Left ── --}}
    <div class="col-lg-8">

        {{-- Basic Info --}}
        <div class="uf-card">
            <div class="uf-card-header">
                <span class="uf-card-icon" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-person"></i></span>
                <div>
                    <div class="uf-card-title">Basic Information</div>
                    <div class="uf-card-sub">Name, email and contact details</div>
                </div>
            </div>
            <div class="uf-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="uf-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="uf-input @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="John Doe" required>
                        @error('name')<div class="uf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Email Address <span class="text-danger">*</span></label>
                        <div class="uf-input-icon">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" class="uf-input @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="john@example.com" required>
                        </div>
                        @error('email')<div class="uf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Mobile Number</label>
                        <div class="uf-input-icon">
                            <i class="bi bi-telephone"></i>
                            <input type="text" name="mobile" class="uf-input @error('mobile') is-invalid @enderror"
                                   value="{{ old('mobile') }}" placeholder="+91 98765 43210">
                        </div>
                        @error('mobile')<div class="uf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Role</label>
                        <select name="role_id" class="uf-input @error('role_id') is-invalid @enderror">
                            <option value="">— Select Role —</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('role_id')<div class="uf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Designation</label>
                        <input type="text" name="designation" class="uf-input"
                               value="{{ old('designation') }}" placeholder="e.g. Sales Manager">
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Department</label>
                        <input type="text" name="department" class="uf-input"
                               value="{{ old('department') }}" placeholder="e.g. Operations">
                    </div>
                </div>
            </div>
        </div>

        {{-- Password Method --}}
        <div class="uf-card">
            <div class="uf-card-header">
                <span class="uf-card-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-shield-lock"></i></span>
                <div>
                    <div class="uf-card-title">Password Setup</div>
                    <div class="uf-card-sub">Choose how the user will receive their password</div>
                </div>
            </div>
            <div class="uf-card-body">

                {{-- Method Selector — 2 options only --}}
                <div class="pw-method-grid">
                    <label class="pw-method-card selected" id="mc-auto" onclick="setPwMethod('auto')">
                        <input type="radio" name="password_method" value="auto" checked class="d-none">
                        <div class="pw-method-icon" style="background:#f0fdf4;color:#16a34a;">
                            <i class="bi bi-magic"></i>
                        </div>
                        <div class="pw-method-label">Auto Generate</div>
                        <div class="pw-method-desc">System generates & emails a secure password</div>
                        <i class="bi bi-check-circle-fill pw-method-check" style="color:#16a34a;"></i>
                    </label>

                    <label class="pw-method-card" id="mc-manual" onclick="setPwMethod('manual')">
                        <input type="radio" name="password_method" value="manual" class="d-none">
                        <div class="pw-method-icon" style="background:#eef2ff;color:#4f46e5;">
                            <i class="bi bi-keyboard"></i>
                        </div>
                        <div class="pw-method-label">Set Manually</div>
                        <div class="pw-method-desc">You define the password, user gets it by email</div>
                        <i class="bi bi-check-circle-fill pw-method-check" style="color:#4f46e5;"></i>
                    </label>
                </div>

                {{-- Auto section --}}
                <div id="section-auto" class="pw-section">
                    <div class="pw-notice pw-notice-green">
                        <i class="bi bi-send-check"></i>
                        <div>
                            <strong>Auto-generated password</strong><br>
                            A strong random password will be created and <strong>emailed in the background</strong> — the page will not wait for the email to send.
                        </div>
                    </div>
                    <div class="pw-preview-wrap">
                        <label class="uf-label">Preview Generated Password</label>
                        <div class="pw-preview-box">
                            <span id="generatedPwDisplay" class="pw-preview-text">—</span>
                            <button type="button" class="pw-gen-btn" onclick="generatePreview()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Regenerate
                            </button>
                        </div>
                        <input type="hidden" name="password" id="autoPassword">
                    </div>
                </div>

                {{-- Manual section --}}
                <div id="section-manual" class="pw-section d-none">
                    <div class="pw-notice pw-notice-blue">
                        <i class="bi bi-info-circle"></i>
                        <div>
                            <strong>Manual password</strong><br>
                            Set a password below. It will be emailed to the user in the background after saving.
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="uf-label">Password <span class="text-danger">*</span></label>
                            <div class="uf-pw-wrap">
                                <input type="password" id="manualPw" name="manual_password"
                                       class="uf-input" placeholder="Min. 8 characters">
                                <button type="button" class="uf-pw-eye" onclick="togglePw('manualPw','eye-manual')">
                                    <i class="bi bi-eye" id="eye-manual"></i>
                                </button>
                            </div>
                            <div class="pw-strength-bar mt-2">
                                <div id="strengthBar" class="pw-strength-fill"></div>
                            </div>
                            <div id="strengthLabel" class="pw-strength-label"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="uf-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="uf-pw-wrap">
                                <input type="password" id="manualPwConfirm" name="manual_password_confirmation"
                                       class="uf-input" placeholder="Re-enter password">
                                <button type="button" class="uf-pw-eye" onclick="togglePw('manualPwConfirm','eye-manual-c')">
                                    <i class="bi bi-eye" id="eye-manual-c"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- ── Right ── --}}
    <div class="col-lg-4">

        {{-- Avatar --}}
        <div class="uf-card">
            <div class="uf-card-header">
                <span class="uf-card-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-image"></i></span>
                <div>
                    <div class="uf-card-title">Profile Photo</div>
                    <div class="uf-card-sub">PNG, JPG — max 2MB</div>
                </div>
            </div>
            <div class="uf-card-body text-center">
                <div class="uf-avatar-preview" id="avatarPreview">
                    <i class="bi bi-person-fill" style="font-size:44px;color:#cbd5e1;"></i>
                </div>
                <label class="uf-upload-btn" for="avatar">
                    <i class="bi bi-cloud-upload me-1"></i> Upload Photo
                </label>
                <input type="file" id="avatar" name="avatar" accept="image/*" class="d-none"
                       onchange="previewAvatar(this)">
                @error('avatar')<div class="uf-error mt-2">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Status --}}
        <div class="uf-card">
            <div class="uf-card-header">
                <span class="uf-card-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-toggle-on"></i></span>
                <div>
                    <div class="uf-card-title">Account Status</div>
                    <div class="uf-card-sub">Set initial account state</div>
                </div>
            </div>
            <div class="uf-card-body">
                @foreach(['active'=>['#16a34a','Active','Fully active'],
                           'inactive'=>['#64748b','Inactive','Account disabled'],
                           'suspended'=>['#dc2626','Suspended','Account suspended']] as $val=>[$color,$label,$desc])
                <label class="uf-status-opt {{ old('status','active')===$val?'selected':'' }}" id="opt-{{ $val }}">
                    <input type="radio" name="status" value="{{ $val }}" class="d-none"
                           {{ old('status','active')===$val?'checked':'' }} onchange="selectStatus('{{ $val }}')">
                    <span class="uf-status-dot" style="background:{{ $color }};"></span>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#1e293b;">{{ $label }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $desc }}</div>
                    </div>
                    <i class="bi bi-check-circle-fill ms-auto uf-status-check" style="color:{{ $color }};"></i>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn btn-primary btn-lg w-100 mb-2" id="submitBtn">
            <i class="bi bi-person-plus me-2"></i><span id="submitLabel">Create User & Send Email</span>
        </button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">Cancel</a>

    </div>
</div>
</form>
@endsection

@push('styles')
<style>
.uf-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);margin-bottom:20px;overflow:hidden}
.uf-card-header{display:flex;align-items:center;gap:14px;padding:16px 22px;border-bottom:1px solid #f1f5f9;background:#fafbfc}
.uf-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.uf-card-title{font-size:14px;font-weight:700;color:#0f172a}
.uf-card-sub{font-size:11.5px;color:#94a3b8;margin-top:1px}
.uf-card-body{padding:22px}
.uf-label{font-size:12.5px;font-weight:700;color:#374151;margin-bottom:6px;display:block}
.uf-input{width:100%;padding:9px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;color:#1e293b;background:#fff;outline:none;transition:border-color .15s,box-shadow .15s}
.uf-input:focus{border-color:var(--primary,#4f46e5);box-shadow:0 0 0 3px rgba(79,70,229,.12)}
.uf-input.is-invalid{border-color:#ef4444}
.uf-error{font-size:11.5px;color:#ef4444;margin-top:4px}
.uf-input-icon{position:relative}
.uf-input-icon i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:14px;pointer-events:none}
.uf-input-icon .uf-input{padding-left:36px}
.uf-pw-wrap{position:relative}
.uf-pw-wrap .uf-input{padding-right:42px}
.uf-pw-eye{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;font-size:15px;padding:2px 4px}
.uf-pw-eye:hover{color:var(--primary,#4f46e5)}
.pw-method-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
.pw-method-card{border:1.5px solid #e2e8f0;border-radius:12px;padding:16px 14px;cursor:pointer;text-align:center;transition:border-color .15s,background .15s,box-shadow .15s;position:relative}
.pw-method-card:hover{border-color:#cbd5e1;background:#fafbfc}
.pw-method-card.selected{border-color:var(--primary,#4f46e5);background:#f8faff;box-shadow:0 0 0 3px rgba(79,70,229,.08)}
.pw-method-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;margin:0 auto 10px}
.pw-method-label{font-size:13px;font-weight:700;color:#1e293b;margin-bottom:4px}
.pw-method-desc{font-size:11px;color:#94a3b8;line-height:1.4}
.pw-method-check{position:absolute;top:8px;right:8px;font-size:15px;display:none}
.pw-method-card.selected .pw-method-check{display:block}
.pw-notice{display:flex;align-items:flex-start;gap:12px;padding:13px 16px;border-radius:10px;font-size:13px;line-height:1.6;margin-bottom:16px}
.pw-notice i{font-size:18px;flex-shrink:0;margin-top:1px}
.pw-notice-green{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d}
.pw-notice-blue{background:#eef2ff;border:1px solid #c7d2fe;color:#3730a3}
.pw-preview-wrap{margin-top:4px}
.pw-preview-box{display:flex;align-items:center;gap:10px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:9px;padding:10px 14px}
.pw-preview-text{flex:1;font-family:monospace;font-size:14px;font-weight:600;color:#1e293b;letter-spacing:1px}
.pw-gen-btn{background:var(--primary,#4f46e5);color:#fff;border:none;border-radius:7px;padding:6px 14px;font-size:12.5px;font-weight:600;cursor:pointer;white-space:nowrap;transition:filter .15s}
.pw-gen-btn:hover{filter:brightness(.9)}
.pw-strength-bar{height:4px;background:#e2e8f0;border-radius:4px;overflow:hidden}
.pw-strength-fill{height:100%;width:0;border-radius:4px;transition:width .3s,background .3s}
.pw-strength-label{font-size:11px;font-weight:600;margin-top:4px}
.uf-avatar-preview{width:90px;height:90px;border-radius:50%;background:#f1f5f9;border:3px dashed #e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;overflow:hidden}
.uf-avatar-preview img{width:100%;height:100%;object-fit:cover}
.uf-upload-btn{display:inline-block;padding:8px 20px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#475569;cursor:pointer;transition:all .15s}
.uf-upload-btn:hover{border-color:var(--primary,#4f46e5);color:var(--primary,#4f46e5);background:#eef2ff}
.uf-status-opt{display:flex;align-items:center;gap:12px;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;margin-bottom:8px;transition:border-color .15s,background .15s}
.uf-status-opt:last-child{margin-bottom:0}
.uf-status-opt.selected{border-color:var(--primary,#4f46e5);background:#f8faff}
.uf-status-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.uf-status-check{display:none;font-size:16px}
.uf-status-opt.selected .uf-status-check{display:block}
</style>
@endpush

@push('scripts')
<script>
let currentMethod = 'auto';

window.addEventListener('DOMContentLoaded', () => generatePreview());

function setPwMethod(method) {
    currentMethod = method;
    ['auto', 'manual'].forEach(m => {
        document.getElementById('mc-' + m).classList.toggle('selected', m === method);
        document.getElementById('section-' + m).classList.toggle('d-none', m !== method);
    });
    document.getElementById('submitLabel').textContent =
        method === 'manual' ? 'Create User & Send Password' : 'Create User & Send Email';
}

function generatePreview() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
    let pw = 'ABCDEFGHJKLMNPQRSTUVWXYZ'[Math.floor(Math.random()*24)]
           + 'abcdefghjkmnpqrstuvwxyz'[Math.floor(Math.random()*23)]
           + '23456789'[Math.floor(Math.random()*8)]
           + '!@#$%'[Math.floor(Math.random()*5)];
    for (let i = 4; i < 12; i++) pw += chars[Math.floor(Math.random() * chars.length)];
    pw = pw.split('').sort(() => Math.random() - 0.5).join('');
    document.getElementById('generatedPwDisplay').textContent = pw;
    document.getElementById('autoPassword').value = pw;
}

function togglePw(fieldId, eyeId) {
    const f = document.getElementById(fieldId);
    const e = document.getElementById(eyeId);
    f.type = f.type === 'password' ? 'text' : 'password';
    e.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

document.getElementById('manualPw').addEventListener('input', function() {
    const v = this.value, bar = document.getElementById('strengthBar'), lbl = document.getElementById('strengthLabel');
    let s = 0;
    if (v.length >= 8) s++; if (v.length >= 12) s++;
    if (/[A-Z]/.test(v)) s++; if (/[0-9]/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
    const lvl = [['0%','#e2e8f0',''],['25%','#ef4444','Weak'],['50%','#f97316','Fair'],
                 ['70%','#eab308','Good'],['85%','#22c55e','Strong'],['100%','#16a34a','Very Strong']][Math.min(s,5)];
    bar.style.width = lvl[0]; bar.style.background = lvl[1];
    lbl.textContent = lvl[2]; lbl.style.color = lvl[1];
});

function previewAvatar(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('avatarPreview').innerHTML = `<img src="${e.target.result}" alt="">`; };
    reader.readAsDataURL(input.files[0]);
}

function selectStatus(val) {
    document.querySelectorAll('.uf-status-opt').forEach(el => el.classList.remove('selected'));
    document.getElementById('opt-' + val).classList.add('selected');
}

document.getElementById('userForm').addEventListener('submit', function(e) {
    if (currentMethod === 'manual') {
        const pw = document.getElementById('manualPw').value;
        const cf = document.getElementById('manualPwConfirm').value;
        if (!pw || pw.length < 8) {
            e.preventDefault();
            return Swal.fire({icon:'error', title:'Password too short', text:'Minimum 8 characters required.', confirmButtonColor:'#4f46e5'});
        }
        if (pw !== cf) {
            e.preventDefault();
            return Swal.fire({icon:'error', title:'Passwords do not match', confirmButtonColor:'#4f46e5'});
        }
    }
    if (currentMethod === 'auto' && !document.getElementById('autoPassword').value) {
        generatePreview();
    }
});
</script>
@endpush
