@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
<style>
.profile-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden;margin-bottom:18px;}
.profile-card-header{display:flex;align-items:center;gap:14px;padding:16px 20px;border-bottom:1px solid #f1f5f9;background:#fafbfc;}
.profile-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.profile-card-title{font-size:14px;font-weight:800;color:#0f172a;}
.profile-card-sub{font-size:11.5px;color:#94a3b8;margin-top:1px;}
.profile-card-body{padding:20px;}
.profile-hero{text-align:center;padding:24px 20px;}
.profile-hero-avatar{width:112px;height:112px;border-radius:50%;object-fit:cover;border:4px solid #e2e8f0;margin-bottom:14px;}
.profile-name{font-size:20px;font-weight:900;color:#0f172a;margin-bottom:2px;}
.profile-role{font-size:13px;color:#64748b;margin-bottom:12px;}
.profile-meta-row{display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #f1f5f9;text-align:left;}
.profile-meta-row:last-child{border-bottom:0;}
.profile-meta-label{font-size:11.5px;color:#94a3b8;font-weight:800;text-transform:uppercase;letter-spacing:.4px;}
.profile-meta-value{font-size:12.5px;color:#1e293b;font-weight:700;text-align:right;}
.pf-label{font-size:12.5px;font-weight:800;color:#374151;margin-bottom:6px;display:block;}
.pf-input{width:100%;padding:9px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;color:#1e293b;background:#fff;outline:none;}
.pf-input:focus{border-color:var(--primary,#4f46e5);box-shadow:0 0 0 3px rgba(79,70,229,.12);}
.pf-help{font-size:11.5px;color:#94a3b8;margin-top:5px;}
.avatar-preview{width:86px;height:86px;border-radius:50%;overflow:hidden;border:3px solid #e2e8f0;margin:0 auto 12px;background:#f8fafc;}
.avatar-preview img{width:100%;height:100%;object-fit:cover;}
.upload-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1.5px solid #e2e8f0;border-radius:8px;color:#475569;background:#f8fafc;font-weight:700;font-size:12.5px;cursor:pointer;}
.login-agent{font-size:12px;color:#64748b;line-height:1.55;}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">My Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">My Profile</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="profile-card">
            <div class="profile-hero">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="profile-hero-avatar">
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-role">{{ $user->designation ?? 'No designation' }}</div>
                {!! $user->status_badge !!}
            </div>
            <div class="profile-card-body pt-0">
                <div class="profile-meta-row">
                    <span class="profile-meta-label">Role</span>
                    <span class="profile-meta-value">{{ $user->role_name }}</span>
                </div>
                <div class="profile-meta-row">
                    <span class="profile-meta-label">Email</span>
                    <span class="profile-meta-value">{{ $user->email }}</span>
                </div>
                <div class="profile-meta-row">
                    <span class="profile-meta-label">Mobile</span>
                    <span class="profile-meta-value">{{ $user->mobile ?? 'N/A' }}</span>
                </div>
                <div class="profile-meta-row">
                    <span class="profile-meta-label">Last Login</span>
                    <span class="profile-meta-value">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                </div>
                <div class="profile-meta-row">
                    <span class="profile-meta-label">Login IP</span>
                    <span class="profile-meta-value">{{ $user->last_login_ip ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-header">
                <span class="profile-card-icon" style="background:#eef2ff;color:var(--primary,#4f46e5);"><i class="bi bi-image"></i></span>
                <div>
                    <div class="profile-card-title">Profile Photo</div>
                    <div class="profile-card-sub">PNG or JPG up to 2MB</div>
                </div>
            </div>
            <div class="profile-card-body text-center">
                <div class="avatar-preview" id="avatarPreview">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                </div>
                <label for="avatar" class="upload-btn">
                    <i class="bi bi-cloud-upload"></i>Choose Photo
                </label>
                <div class="pf-help">Photo is saved when you update profile.</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <form id="profileForm" action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" id="avatar" name="avatar" accept="image/*" class="d-none" onchange="previewAvatar(this)">

            <div class="profile-card">
                <div class="profile-card-header">
                    <span class="profile-card-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-person"></i></span>
                    <div>
                        <div class="profile-card-title">Profile Information</div>
                        <div class="profile-card-sub">Update your personal and contact details</div>
                    </div>
                </div>
                <div class="profile-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="pf-label">Full Name</label>
                            <input type="text" name="name" class="pf-input" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="pf-label">Email Address</label>
                            <input type="email" class="pf-input" value="{{ $user->email }}" disabled>
                            <div class="pf-help">Email is managed by administrator.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="pf-label">Mobile Number</label>
                            <input type="text" name="mobile" class="pf-input" value="{{ old('mobile', $user->mobile) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="pf-label">Designation</label>
                            <input type="text" name="designation" class="pf-input" value="{{ old('designation', $user->designation) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="pf-label">Department</label>
                            <input type="text" name="department" class="pf-input" value="{{ old('department', $user->department) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="pf-label">City</label>
                            <input type="text" name="city" class="pf-input" value="{{ old('city', $user->city) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="pf-label">State</label>
                            <input type="text" name="state" class="pf-input" value="{{ old('state', $user->state) }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="dark_mode" value="1" id="darkMode" {{ $user->dark_mode ? 'checked' : '' }}>
                                <label class="form-check-label fw-700" for="darkMode">Prefer dark mode</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="pf-label">Address</label>
                            <textarea name="address" class="pf-input" rows="3">{{ old('address', $user->address) }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Save Profile
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="profile-card">
            <div class="profile-card-header">
                <span class="profile-card-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-shield-lock"></i></span>
                <div>
                    <div class="profile-card-title">Change Password</div>
                    <div class="profile-card-sub">Use a strong password for your account</div>
                </div>
            </div>
            <div class="profile-card-body">
                <form id="passwordForm" action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="pf-label">Current Password</label>
                            <input type="password" name="current_password" class="pf-input" required>
                        </div>
                        <div class="col-md-4">
                            <label class="pf-label">New Password</label>
                            <input type="password" name="password" class="pf-input" minlength="8" required>
                        </div>
                        <div class="col-md-4">
                            <label class="pf-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="pf-input" minlength="8" required>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-key me-1"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-header">
                <span class="profile-card-icon" style="background:#e0f2fe;color:#0284c7;"><i class="bi bi-door-open"></i></span>
                <div>
                    <div class="profile-card-title">Recent Login History</div>
                    <div class="profile-card-sub">Latest sessions recorded for your account</div>
                </div>
            </div>
            <div class="table-responsive mobile-table-wrap">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Device</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Logout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loginHistories as $history)
                        <tr>
                            <td data-label="Date & Time">
                                <strong style="font-size:12px;">{{ $history->login_at->format('d M Y, h:i A') }}</strong>
                                <div class="text-muted" style="font-size:11.5px;">{{ $history->login_at->diffForHumans() }}</div>
                            </td>
                            <td data-label="Device">
                                <div class="login-agent">
                                    <strong>{{ $history->device ?: 'Unknown' }}</strong>
                                    <div>{{ $history->browser ?: 'Unknown Browser' }} on {{ $history->platform ?: 'Unknown OS' }}</div>
                                </div>
                            </td>
                            <td data-label="IP Address"><small class="text-muted">{{ $history->ip_address ?? 'N/A' }}</small></td>
                            <td data-label="Status">{!! $history->status_badge !!}</td>
                            <td data-label="Logout">
                                @if($history->logout_at)
                                    <small>{{ $history->logout_at->format('d M Y, h:i A') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>No login history found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <x-pagination :paginator="$loginHistories" label="records" />
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatarPreview').innerHTML = `<img src="${e.target.result}" alt="Preview">`;
    };
    reader.readAsDataURL(input.files[0]);
}

function submitAjax(form, successTitle) {
    const data = new FormData(form);
    $.ajax({
        url: form.action,
        method: 'POST',
        data,
        processData: false,
        contentType: false,
        success: res => Swal.fire({ icon:'success', title:successTitle, text:res.message, timer:2200, showConfirmButton:false })
            .then(() => location.reload()),
        error: xhr => {
            const errors = xhr.responseJSON?.errors;
            const message = errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || 'Something went wrong.');
            Swal.fire('Error', message, 'error');
        }
    });
}

$('#profileForm').on('submit', function(e) {
    e.preventDefault();
    submitAjax(this, 'Profile saved');
});

$('#passwordForm').on('submit', function(e) {
    e.preventDefault();
    submitAjax(this, 'Password updated');
});
</script>
@endpush
