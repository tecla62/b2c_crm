@extends('layouts.app')

@section('title', $customer->name)

@push('styles')
<style>
/* ── Cover Banner ── */
.customer-cover {
    height: 140px;
    background: linear-gradient(135deg, var(--primary) 0%, #818cf8 100%);
    border-radius: 14px 14px 0 0;
    position: relative;
}

/* ── Profile Card ── */
.profile-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    overflow: hidden;
    margin-bottom: 22px;
}
.profile-card-body {
    padding: 0 24px 24px;
    position: relative;
}
.profile-avatar-wrap {
    position: relative;
    display: inline-block;
    margin-top: -44px;
    margin-bottom: 12px;
}
.profile-avatar-wrap img {
    width: 88px; height: 88px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    display: block;
}
.profile-status-dot {
    position: absolute;
    bottom: 6px; right: 6px;
    width: 14px; height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
}
.profile-name { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 2px; }
.profile-company { font-size: 13px; color: #64748b; margin: 0 0 12px; }

/* ── Quick Actions ── */
.quick-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
.qa-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12.5px; font-weight: 600;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: all .18s;
    cursor: pointer;
    background: none;
}
.qa-btn-primary { background: var(--primary); color: #fff; border-color: var(--primary); }
.qa-btn-primary:hover { filter: brightness(.9); color: #fff; transform: translateY(-1px); }
.qa-btn-outline { border-color: var(--border); color: #475569; background: #f8fafc; }
.qa-btn-outline:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
.qa-btn-danger { border-color: #fecaca; color: #dc2626; background: #fef2f2; }
.qa-btn-danger:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

/* ── Stats Row ── */
.profile-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    border-top: 1px solid #f1f5f9;
    margin: 0 -24px;
    padding: 0 24px;
}
.profile-stat {
    text-align: center;
    padding: 14px 8px;
    border-right: 1px solid #f1f5f9;
}
.profile-stat:last-child { border-right: none; }
.profile-stat-value { font-size: 15px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.profile-stat-label { font-size: 10.5px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }

/* ── Info Section ── */
.info-section {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    margin-bottom: 22px;
    overflow: hidden;
}
.info-section-header {
    display: flex; align-items: center; gap: 10px;
    padding: 15px 22px;
    border-bottom: 1px solid #f1f5f9;
}
.info-section-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
}
.info-section-title { font-size: 13.5px; font-weight: 700; color: #1e293b; margin: 0; }
.info-section-body { padding: 20px 22px; }

/* ── Info Row ── */
.info-row {
    display: flex; align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid #f8fafc;
    gap: 12px;
}
.info-row:last-child { border-bottom: none; padding-bottom: 0; }
.info-row:first-child { padding-top: 0; }
.info-row-icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0; margin-top: 1px;
}
.info-row-content { flex: 1; min-width: 0; }
.info-row-label { font-size: 10.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
.info-row-value { font-size: 13.5px; font-weight: 600; color: #1e293b; word-break: break-word; }
.info-row-value a { color: var(--primary); text-decoration: none; }
.info-row-value a:hover { text-decoration: underline; }
.info-row-value.empty { color: #cbd5e1; font-weight: 400; }

/* ── Address Map Style ── */
.address-block {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 16px;
    font-size: 13.5px;
    color: #1e293b;
    line-height: 1.7;
}

/* ── Notes block ── */
.notes-block {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    padding: 14px 16px;
    font-size: 13.5px;
    color: #78350f;
    line-height: 1.7;
    white-space: pre-line;
}

/* ── Meta card ── */
.meta-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    margin-bottom: 22px;
    overflow: hidden;
}
.meta-item {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 20px;
    border-bottom: 1px solid #f8fafc;
}
.meta-item:last-child { border-bottom: none; }
.meta-item-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; flex-shrink: 0;
}
.meta-item-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
.meta-item-value { font-size: 13px; font-weight: 600; color: #1e293b; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Customer Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item active">{{ $customer->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('customers.edit'))
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>Edit Customer
        </a>
        @endif
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-4">

        {{-- Profile Card --}}
        <div class="profile-card">
            <div class="customer-cover">
                {{-- Status pill on cover --}}
                <div style="position:absolute;top:14px;right:14px;">
                    {!! $customer->status_badge !!}
                </div>
                {{-- Type pill --}}
                <div style="position:absolute;top:14px;left:14px;">
                    {!! $customer->type_badge !!}
                </div>
            </div>
            <div class="profile-card-body">
                <div class="profile-avatar-wrap">
                    <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}">
                    @php
                        $dotColor = match($customer->status) {
                            'active'   => '#22c55e',
                            'inactive' => '#94a3b8',
                            'blocked'  => '#ef4444',
                            default    => '#94a3b8'
                        };
                    @endphp
                    <span class="profile-status-dot" style="background:{{ $dotColor }};"></span>
                </div>

                <h5 class="profile-name">{{ $customer->name }}</h5>
                <p class="profile-company">
                    @if($customer->company_name)
                        <i class="bi bi-building me-1"></i>{{ $customer->company_name }}
                    @else
                        <span class="text-muted">No company</span>
                    @endif
                </p>

                {{-- Quick Actions --}}
                <div class="quick-actions">
                    @if(auth()->user()->hasPermission('customers.edit'))
                    <a href="{{ route('admin.customers.edit', $customer) }}" class="qa-btn qa-btn-primary">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a>
                    @endif
                    @if($customer->email)
                    <a href="mailto:{{ $customer->email }}" class="qa-btn qa-btn-outline">
                        <i class="bi bi-envelope"></i> Email
                    </a>
                    @endif
                    @if($customer->mobile)
                    <a href="tel:{{ $customer->mobile }}" class="qa-btn qa-btn-outline">
                        <i class="bi bi-telephone"></i> Call
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('customers.delete'))
                    <button onclick="deleteCustomer({{ $customer->id }})" class="qa-btn qa-btn-danger">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                    @endif
                </div>

                {{-- Stats --}}
                <div class="profile-stats">
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $customer->created_at->format('d M') }}</div>
                        <div class="profile-stat-label">Joined</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $customer->source ? ucfirst($customer->source) : '—' }}</div>
                        <div class="profile-stat-label">Source</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $customer->assignedTo?->name ? explode(' ', $customer->assignedTo->name)[0] : '—' }}</div>
                        <div class="profile-stat-label">Assigned</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Meta Info --}}
        <div class="meta-card">
            <div class="meta-item">
                <div class="meta-item-icon" style="background:#eef2ff;color:#4f46e5;">
                    <i class="bi bi-calendar-plus"></i>
                </div>
                <div>
                    <div class="meta-item-label">Created On</div>
                    <div class="meta-item-value">{{ $customer->created_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-item-icon" style="background:#f0fdf4;color:#16a34a;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <div class="meta-item-label">Last Updated</div>
                    <div class="meta-item-value">{{ $customer->updated_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
            @if($customer->createdBy)
            <div class="meta-item">
                <div class="meta-item-icon" style="background:#fff7ed;color:#ea580c;">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div>
                    <div class="meta-item-label">Added By</div>
                    <div class="meta-item-value">{{ $customer->createdBy->name }}</div>
                </div>
            </div>
            @endif
            @if($customer->assignedTo)
            <div class="meta-item">
                <div class="meta-item-icon" style="background:#fdf4ff;color:#9333ea;">
                    <i class="bi bi-person-check"></i>
                </div>
                <div>
                    <div class="meta-item-label">Assigned To</div>
                    <div class="meta-item-value">{{ $customer->assignedTo->name }}</div>
                </div>
            </div>
            @endif
        </div>

    </div>

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- Contact Information --}}
        <div class="info-section">
            <div class="info-section-header">
                <div class="info-section-icon" style="background:#eef2ff;color:#4f46e5;">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <h6 class="info-section-title">Contact Information</h6>
            </div>
            <div class="info-section-body">
                <div class="info-row">
                    <div class="info-row-icon" style="background:#f0f9ff;color:#0284c7;">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div class="info-row-content">
                        <div class="info-row-label">Email Address</div>
                        <div class="info-row-value">
                            @if($customer->email)
                                <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                            @else
                                <span class="empty">Not provided</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-icon" style="background:#f0fdf4;color:#16a34a;">
                        <i class="bi bi-phone-fill"></i>
                    </div>
                    <div class="info-row-content">
                        <div class="info-row-label">Mobile Number</div>
                        <div class="info-row-value">
                            @if($customer->mobile)
                                <a href="tel:{{ $customer->mobile }}">{{ $customer->mobile }}</a>
                            @else
                                <span class="empty">Not provided</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-icon" style="background:#fff7ed;color:#ea580c;">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div class="info-row-content">
                        <div class="info-row-label">Phone / Landline</div>
                        <div class="info-row-value">
                            @if($customer->phone)
                                <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                            @else
                                <span class="empty">Not provided</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-icon" style="background:#fdf4ff;color:#9333ea;">
                        <i class="bi bi-globe2"></i>
                    </div>
                    <div class="info-row-content">
                        <div class="info-row-label">Website</div>
                        <div class="info-row-value">
                            @if($customer->website)
                                <a href="{{ $customer->website }}" target="_blank">{{ $customer->website }}</a>
                            @else
                                <span class="empty">Not provided</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Company Information --}}
        <div class="info-section">
            <div class="info-section-header">
                <div class="info-section-icon" style="background:#f0fdf4;color:#16a34a;">
                    <i class="bi bi-building"></i>
                </div>
                <h6 class="info-section-title">Company Information</h6>
            </div>
            <div class="info-section-body">
                <div class="info-row">
                    <div class="info-row-icon" style="background:#f0fdf4;color:#16a34a;">
                        <i class="bi bi-building-fill"></i>
                    </div>
                    <div class="info-row-content">
                        <div class="info-row-label">Company Name</div>
                        <div class="info-row-value {{ !$customer->company_name ? 'empty' : '' }}">
                            {{ $customer->company_name ?? 'Not provided' }}
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-icon" style="background:#fefce8;color:#ca8a04;">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div class="info-row-content">
                        <div class="info-row-label">GST Number</div>
                        <div class="info-row-value {{ !$customer->gst_number ? 'empty' : '' }}">
                            {{ $customer->gst_number ?? 'Not provided' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="info-section">
            <div class="info-section-header">
                <div class="info-section-icon" style="background:#fff7ed;color:#ea580c;">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h6 class="info-section-title">Address</h6>
            </div>
            <div class="info-section-body">
                @php
                    $addressParts = array_filter([
                        $customer->address,
                        $customer->city,
                        $customer->state,
                        $customer->pincode,
                        $customer->country,
                    ]);
                @endphp
                @if(count($addressParts))
                <div class="address-block">
                    <i class="bi bi-geo-alt text-muted me-1"></i>
                    {{ implode(', ', $addressParts) }}
                </div>
                <div class="row g-3 mt-2">
                    @foreach([
                        ['City', $customer->city],
                        ['State', $customer->state],
                        ['Pincode', $customer->pincode],
                        ['Country', $customer->country],
                    ] as [$lbl, $val])
                    <div class="col-6 col-md-3">
                        <div class="info-row-label">{{ $lbl }}</div>
                        <div class="info-row-value {{ !$val ? 'empty' : '' }}">{{ $val ?? '—' }}</div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted fs-13 mb-0"><i class="bi bi-geo-alt me-1"></i>No address provided</p>
                @endif
            </div>
        </div>

        {{-- Notes --}}
        @if($customer->notes)
        <div class="info-section">
            <div class="info-section-header">
                <div class="info-section-icon" style="background:#fffbeb;color:#d97706;">
                    <i class="bi bi-sticky-fill"></i>
                </div>
                <h6 class="info-section-title">Notes</h6>
            </div>
            <div class="info-section-body">
                <div class="notes-block">{{ $customer->notes }}</div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteCustomer(id) {
    Swal.fire({
        title: 'Delete this customer?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete!'
    }).then(result => {
        if (result.isConfirmed) {
            const customerDeleteUrl = id => '{{ route("admin.customers.destroy", ["customer" => "__CUSTOMER__"]) }}'.replace('__CUSTOMER__', id);
            $.ajax({
                url: customerDeleteUrl(id), type: 'DELETE',
                success: r => Swal.fire('Deleted!', r.message, 'success')
                    .then(() => window.location.href = '{{ route('admin.customers.index') }}'),
                error: x => Swal.fire('Error!', x.responseJSON?.message || 'Something went wrong', 'error')
            });
        }
    });
}
</script>
@endpush
