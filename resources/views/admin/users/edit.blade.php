@extends('layouts.app')
@section('title', 'Edit User — ' . $user->name)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Edit User</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">{{ $user->name }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Users
    </a>
</div>

<form id="editForm" method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="row g-4">

    {{-- ── Left Column ── --}}
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
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="uf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Email Address <span class="text-danger">*</span></label>
                        <div class="uf-input-icon">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" class="uf-input @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                        </div>
                        @error('email')<div class="uf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Mobile Number</label>
                        <div class="uf-input-icon">
                            <i class="bi bi-telephone"></i>
                            <input type="text" name="mobile" class="uf-input @error('mobile') is-invalid @enderror"
                                   value="{{ old('mobile', $user->mobile) }}">
                        </div>
                        @error('mobile')<div class="uf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Role</label>
                        <select name="role_id" class="uf-input @error('role_id') is-invalid @enderror">
                            <option value="">— Select Role —</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('role_id')<div class="uf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Designation</label>
                        <input type="text" name="designation" class="uf-input"
                               value="{{ old('designation', $user->designation) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Department</label>
                        <input type="text" name="department" class="uf-input"
                               value="{{ old('department', $user->department) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Password --}}
        <div class="uf-card">
            <div class="uf-card-header">
                <span class="uf-card-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-shield-lock"></i></span>
                <div>
                    <div class="uf-card-title">Change Password</div>
                    <div class="uf-card-sub">Leave blank to keep current password</div>
                </div>
            </div>
            <div class="uf-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="uf-label">New Password</label>
                        <div class="uf-pw-wrap">
                            <input type="password" id="password" name="password"
                                   class="uf-input @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 characters">
                            <button type="button" class="uf-pw-eye" onclick="togglePw('password')">
                                <i class="bi bi-eye" id="eye-password"></i>
                            </button>
                        </div>
                        @error('password')<div class="uf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="uf-label">Confirm New Password</label>
                        <div class="uf-pw-wrap">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="uf-input" placeholder="Re-enter new password">
                            <button type="button" class="uf-pw-eye" onclick="togglePw('password_confirmation')">
                                <i class="bi bi-eye" id="eye-password_confirmation"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Right Column ── --}}
    <div class="col-lg-4">

        {{-- User Info Card --}}
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
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                </div>
                <label class="uf-upload-btn" for="avatar">
                    <i class="bi bi-cloud-upload me-1"></i> Change Photo
                </label>
                <input type="file" id="avatar" name="avatar" accept="image/*" class="d-none"
                       onchange="previewAvatar(this)">
                @error('avatar')<div class="uf-error mt-2">{{ $message }}</div>@enderror

                {{-- Meta info --}}
                <div class="uf-meta-table mt-3">
                    <div class="uf-meta-row">
                        <span class="uf-meta-label">User ID</span>
                        <span class="uf-meta-value">#{{ $user->id }}</span>
                    </div>
                    <div class="uf-meta-row">
                        <span class="uf-meta-label">Created</span>
                        <span class="uf-meta-value">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="uf-meta-row">
                        <span class="uf-meta-label">Last Login</span>
                        <span class="uf-meta-value">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="uf-card">
            <div class="uf-card-header">
                <span class="uf-card-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-toggle-on"></i></span>
                <div>
                    <div class="uf-card-title">Account Status</div>
                    <div class="uf-card-sub">Current account state</div>
                </div>
            </div>
            <div class="uf-card-body">
                @foreach(['active' => ['#16a34a','Active','Account is fully active'],
                           'inactive' => ['#64748b','Inactive','Account is disabled'],
                           'suspended' => ['#dc2626','Suspended','Account is suspended']] as $val => [$color,$label,$desc])
                <label class="uf-status-opt {{ old('status', $user->status) === $val ? 'selected' : '' }}" id="opt-{{ $val }}">
                    <input type="radio" name="status" value="{{ $val }}" class="d-none"
                           {{ old('status', $user->status) === $val ? 'checked' : '' }}
                           onchange="selectStatus('{{ $val }}')">
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

        {{-- Actions --}}
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle me-2"></i>Save Changes
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

        {{-- Danger Zone --}}
        @if(auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
        <div class="uf-card mt-3" style="border:1.5px solid #fee2e2;">
            <div class="uf-card-header" style="background:#fff5f5;">
                <span class="uf-card-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-exclamation-triangle"></i></span>
                <div>
                    <div class="uf-card-title" style="color:#dc2626;">Danger Zone</div>
                    <div class="uf-card-sub">Irreversible actions</div>
                </div>
            </div>
            <div class="uf-card-body">
                <button type="button" onclick="deleteUser({{ $user->id }})"
                        class="btn btn-danger w-100">
                    <i class="bi bi-trash me-2"></i>Delete This User
                </button>
            </div>
        </div>
        @endif

    </div>
</div>
</form>
@endsection

@push('styles')
<style>
.uf-card { background:#fff; border-radius:14px; box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04); margin-bottom:20px; overflow:hidden; }
.uf-card-header { display:flex; align-items:center; gap:14px; padding:16px 22px; border-bottom:1px solid #f1f5f9; background:#fafbfc; }
.uf-card-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
.uf-card-title { font-size:14px; font-weight:700; color:#0f172a; }
.uf-card-sub   { font-size:11.5px; color:#94a3b8; margin-top:1px; }
.uf-card-body  { padding:22px; }

.uf-label { font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block; }
.uf-input {
    width:100%; padding:9px 13px; border:1.5px solid #e2e8f0; border-radius:9px;
    font-size:13.5px; color:#1e293b; background:#fff; outline:none;
    transition:border-color .15s, box-shadow .15s;
}
.uf-input:focus { border-color:var(--primary,#4f46e5); box-shadow:0 0 0 3px rgba(79,70,229,.12); }
.uf-input.is-invalid { border-color:#ef4444; }
.uf-error { font-size:11.5px; color:#ef4444; margin-top:4px; }

.uf-input-icon { position:relative; }
.uf-input-icon i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:14px; pointer-events:none; }
.uf-input-icon .uf-input { padding-left:36px; }

.uf-pw-wrap { position:relative; }
.uf-pw-wrap .uf-input { padding-right:42px; }
.uf-pw-eye { position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; cursor:pointer; font-size:15px; padding:2px 4px; }
.uf-pw-eye:hover { color:var(--primary,#4f46e5); }

.uf-avatar-preview {
    width:90px; height:90px; border-radius:50%; background:#f1f5f9;
    border:3px solid #e2e8f0; display:flex; align-items:center; justify-content:center;
    margin:0 auto 14px; overflow:hidden;
}
.uf-avatar-preview img { width:100%; height:100%; object-fit:cover; }
.uf-upload-btn {
    display:inline-block; padding:8px 20px; background:#f8fafc;
    border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px;
    font-weight:600; color:#475569; cursor:pointer; transition:all .15s;
}
.uf-upload-btn:hover { border-color:var(--primary,#4f46e5); color:var(--primary,#4f46e5); background:#eef2ff; }

.uf-meta-table { text-align:left; }
.uf-meta-row { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #f1f5f9; }
.uf-meta-row:last-child { border-bottom:none; }
.uf-meta-label { font-size:11.5px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.4px; }
.uf-meta-value { font-size:12.5px; color:#1e293b; font-weight:500; }

.uf-status-opt {
    display:flex; align-items:center; gap:12px; padding:12px 14px;
    border:1.5px solid #e2e8f0; border-radius:10px; cursor:pointer;
    margin-bottom:8px; transition:border-color .15s, background .15s;
}
.uf-status-opt:last-child { margin-bottom:0; }
.uf-status-opt.selected { border-color:var(--primary,#4f46e5); background:#f8faff; }
.uf-status-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.uf-status-check { display:none; font-size:16px; }
.uf-status-opt.selected .uf-status-check { display:block; }
</style>
@endpush

@push('scripts')
<script>
function togglePw(id) {
    const f = document.getElementById(id);
    const e = document.getElementById('eye-' + id);
    f.type = f.type === 'password' ? 'text' : 'password';
    e.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function previewAvatar(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatarPreview').innerHTML =
            `<img src="${e.target.result}" alt="Preview">`;
    };
    reader.readAsDataURL(input.files[0]);
}

function selectStatus(val) {
    document.querySelectorAll('.uf-status-opt').forEach(el => el.classList.remove('selected'));
    document.getElementById('opt-' + val).classList.add('selected');
}

const deleteUrlTemplate = '{{ route("admin.users.destroy", ["user" => "__USER_ID__"]) }}';
const usersIndexUrl = '{{ route('admin.users.index') }}';

function deleteUser(id) {
    Swal.fire({
        title: 'Delete this user?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete!'
    }).then(r => {
        if (r.isConfirmed) {
            $.ajax({
                url: deleteUrlTemplate.replace('__USER_ID__', id),
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: res => Swal.fire('Deleted!', res.message, 'success')
                    .then(() => window.location.href = usersIndexUrl),
                error: x => Swal.fire('Error!', x.responseJSON?.message || 'Something went wrong', 'error')
            });
        }
    });
}
</script>
@endpush
