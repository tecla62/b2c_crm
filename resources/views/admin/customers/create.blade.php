@extends('layouts.app')

@section('title', 'Add Customer')

@push('styles')
<style>
.form-section {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    margin-bottom: 22px;
    overflow: hidden;
}
.form-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 22px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafbfc;
}
.form-section-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.form-section-title { font-size: 14px; font-weight: 700; color: #1e293b; margin: 0; }
.form-section-body { padding: 22px; }

.form-label { font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-control, .form-select {
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    font-size: 13.5px;
    padding: 9px 13px;
    transition: all .2s;
    background: #fff;
}
.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,70,229,.1);
}
.form-control.is-invalid { border-color: #ef4444; }
.input-icon-wrap { position: relative; }
.input-icon-wrap .input-icon {
    position: absolute; left: 12px; top: 50%;
    transform: translateY(-50%);
    color: #94a3b8; font-size: 14px; pointer-events: none;
}
.input-icon-wrap .form-control,
.input-icon-wrap .form-select { padding-left: 36px; }

/* Avatar upload */
.avatar-upload-box {
    border: 2px dashed #e2e8f0;
    border-radius: 14px;
    padding: 24px 16px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    background: #fafbfc;
}
.avatar-upload-box:hover { border-color: var(--primary); background: #f5f3ff; }
.avatar-upload-box img {
    width: 80px; height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e2e8f0;
    margin-bottom: 10px;
}

/* Sticky sidebar */
.sticky-sidebar { position: sticky; top: 80px; }

/* Submit btn */
.btn-submit {
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 13px 28px;
    font-size: 14px;
    font-weight: 700;
    width: 100%;
    transition: all .2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-submit:hover { filter: brightness(.92); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(79,70,229,.3); }

.required-star { color: #ef4444; margin-left: 2px; }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Add Customer</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item active">Add New</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data" id="customerForm">
@csrf
<div class="row g-4">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- Basic Info --}}
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon" style="background:#eef2ff;color:#4f46e5;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h6 class="form-section-title">Basic Information</h6>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="required-star">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Enter full name">
                        </div>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="email@example.com">
                        </div>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-phone input-icon"></i>
                            <input type="text" name="mobile"
                                   class="form-control @error('mobile') is-invalid @enderror"
                                   value="{{ old('mobile') }}" placeholder="+91 XXXXX XXXXX">
                        </div>
                        @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone / Landline</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-telephone input-icon"></i>
                            <input type="text" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" placeholder="Landline number">
                        </div>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer Type <span class="required-star">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-tag input-icon"></i>
                            <select name="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="individual" {{ old('type','individual') == 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="company"    {{ old('type') == 'company' ? 'selected' : '' }}>Company</option>
                            </select>
                        </div>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status <span class="required-star">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-toggle-on input-icon"></i>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active"   {{ old('status','active') == 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="blocked"  {{ old('status') == 'blocked'  ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Company Info --}}
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon" style="background:#f0fdf4;color:#16a34a;">
                    <i class="bi bi-building"></i>
                </div>
                <h6 class="form-section-title">Company Information</h6>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-building input-icon"></i>
                            <input type="text" name="company_name"
                                   class="form-control @error('company_name') is-invalid @enderror"
                                   value="{{ old('company_name') }}" placeholder="Business / Company name">
                        </div>
                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">GST Number</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-file-earmark-text input-icon"></i>
                            <input type="text" name="gst_number"
                                   class="form-control @error('gst_number') is-invalid @enderror"
                                   value="{{ old('gst_number') }}" placeholder="22AAAAA0000A1Z5">
                        </div>
                        @error('gst_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Website</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-globe input-icon"></i>
                            <input type="url" name="website"
                                   class="form-control @error('website') is-invalid @enderror"
                                   value="{{ old('website') }}" placeholder="https://example.com">
                        </div>
                        @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon" style="background:#fff7ed;color:#ea580c;">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h6 class="form-section-title">Address Details</h6>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Street Address</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-house input-icon"></i>
                            <input type="text" name="address"
                                   class="form-control @error('address') is-invalid @enderror"
                                   value="{{ old('address') }}" placeholder="Door no, Street, Area">
                        </div>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control"
                               value="{{ old('city') }}" placeholder="City">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control"
                               value="{{ old('state') }}" placeholder="State">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control"
                               value="{{ old('pincode') }}" placeholder="600001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control"
                               value="{{ old('country', 'India') }}" placeholder="Country">
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon" style="background:#fdf4ff;color:#9333ea;">
                    <i class="bi bi-chat-left-text-fill"></i>
                </div>
                <h6 class="form-section-title">Notes</h6>
            </div>
            <div class="form-section-body">
                <textarea name="notes" class="form-control" rows="4"
                          placeholder="Any additional notes about this customer…">{{ old('notes') }}</textarea>
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">
        <div class="sticky-sidebar">

            {{-- Avatar --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-icon" style="background:#f0f9ff;color:#0284c7;">
                        <i class="bi bi-camera-fill"></i>
                    </div>
                    <h6 class="form-section-title">Profile Photo</h6>
                </div>
                <div class="form-section-body">
                    <div class="avatar-upload-box" onclick="$('#avatarInput').click()">
                        <img id="avatarPreview"
                             src="https://ui-avatars.com/api/?name=C&background=0ea5e9&color=fff&size=128"
                             alt="Preview">
                        <p style="font-size:13px;color:#64748b;margin:0 0 4px;font-weight:600;">Click to upload photo</p>
                        <p style="font-size:11px;color:#94a3b8;margin:0;">JPG, PNG up to 2MB</p>
                    </div>
                    <input type="file" name="avatar" id="avatarInput" class="d-none @error('avatar') is-invalid @enderror" accept="image/*">
                    @error('avatar')<div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Source & Assignment --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-icon" style="background:#fefce8;color:#ca8a04;">
                        <i class="bi bi-sliders2"></i>
                    </div>
                    <h6 class="form-section-title">Assignment & Source</h6>
                </div>
                <div class="form-section-body">
                    <div class="mb-3">
                        <label class="form-label">Lead Source</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-signpost input-icon"></i>
                            <select name="source" class="form-select @error('source') is-invalid @enderror">
                                <option value="">— Select Source —</option>
                                @foreach(['referral','website','walk-in','social media','advertisement','other'] as $src)
                                <option value="{{ $src }}" {{ old('source') == $src ? 'selected' : '' }}>
                                    {{ ucfirst($src) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Assigned To (Sales Executive / Staff)</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-person-check input-icon"></i>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">— Unassigned —</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role_name }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle-fill"></i> Save Customer
            </button>
            <a href="{{ route('admin.customers.index') }}"
               class="btn btn-outline-secondary w-100 mt-2" style="border-radius:10px;padding:11px;">
                Cancel
            </a>

        </div>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
$('#avatarInput').on('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            $('#avatarPreview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
