@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Edit Customer</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form action="{{ route('admin.customers.update', $customer) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="row">

    <!-- Left Column -->
    <div class="col-lg-8">

        <!-- Basic Info -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-person me-2"></i>Basic Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $customer->name) }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $customer->email) }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Mobile</label>
                        <input type="text" name="mobile" class="form-control"
                               value="{{ old('mobile', $customer->mobile) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $customer->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select">
                            <option value="individual" {{ old('type', $customer->type) == 'individual' ? 'selected' : '' }}>Individual</option>
                            <option value="company"    {{ old('type', $customer->type) == 'company'    ? 'selected' : '' }}>Company</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select">
                            <option value="active"   {{ old('status', $customer->status) == 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="blocked"  {{ old('status', $customer->status) == 'blocked'  ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Info -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-building me-2"></i>Company Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600">Company Name</label>
                        <input type="text" name="company_name" class="form-control"
                               value="{{ old('company_name', $customer->company_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">GST Number</label>
                        <input type="text" name="gst_number" class="form-control"
                               value="{{ old('gst_number', $customer->gst_number) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Website</label>
                        <input type="url" name="website" class="form-control"
                               value="{{ old('website', $customer->website) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-geo-alt me-2"></i>Address</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600">Address</label>
                        <input type="text" name="address" class="form-control"
                               value="{{ old('address', $customer->address) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $customer->state) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $customer->pincode) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', $customer->country) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-chat-text me-2"></i>Notes</div>
            <div class="card-body">
                <textarea name="notes" class="form-control" rows="4">{{ old('notes', $customer->notes) }}</textarea>
            </div>
        </div>

    </div>

    <!-- Right Column -->
    <div class="col-lg-4">

        <!-- Avatar -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-image me-2"></i>Profile Photo</div>
            <div class="card-body text-center">
                <img id="avatarPreview" src="{{ $customer->avatar_url }}"
                     class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                <input type="file" name="avatar" id="avatarInput" class="form-control" accept="image/*">
                <small class="text-muted d-block mt-1">Leave blank to keep current photo</small>
            </div>
        </div>

        <!-- Source & Assignment -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-sliders me-2"></i>Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-600">Lead Source</label>
                    <select name="source" class="form-select">
                        <option value="">— Select Source —</option>
                        @foreach(['referral','website','walk-in','social media','advertisement','other'] as $src)
                        <option value="{{ $src }}" {{ old('source', $customer->source) == $src ? 'selected' : '' }}>
                            {{ ucfirst($src) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Assigned To (Sales Executive / Staff)</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">— Unassigned —</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to', $customer->assigned_to) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->role_name }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-circle me-2"></i>Update Customer
        </button>
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
        reader.onload = e => $('#avatarPreview').attr('src', e.target.result);
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
