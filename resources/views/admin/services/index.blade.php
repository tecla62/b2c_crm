@extends('layouts.app')
@section('title', 'Service Master')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Service Master</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Services</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('services.view'))
        <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-tags me-1"></i>Categories
        </a>
        @endif
        @if(auth()->user()->hasPermission('services.create'))
        <button class="btn btn-primary" onclick="openModal()">
            <i class="bi bi-plus-circle me-2"></i>Add Service
        </button>
        @endif
    </div>
</div>

{{-- Stats --}}
@php
    $total    = \App\Models\Service::count();
    $active   = \App\Models\Service::where('status','active')->count();
    $inactive = \App\Models\Service::where('status','inactive')->count();
@endphp
<div class="row g-3 mb-4">
    @foreach([
        ['Total Services', $total,    'bi-grid-fill',        '#eef2ff','#4f46e5'],
        ['Active',         $active,   'bi-check-circle-fill','#f0fdf4','#16a34a'],
        ['Inactive',       $inactive, 'bi-pause-circle-fill','#f8fafc','#94a3b8'],
    ] as [$label,$value,$icon,$bg,$color])
    <div class="col-6 col-md-4">
        <div class="stat-card" style="border-left-color:{{ $color }};">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:{{ $bg }};color:{{ $color }};"><i class="bi {{ $icon }}"></i></div>
                <div>
                    <div class="stat-value" style="font-size:20px;">{{ $value }}</div>
                    <div class="stat-label">{{ $label }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:260px;"
                   placeholder="Search services…" value="{{ request('search') }}">

            <select id="categoryFilter" class="form-select" style="max-width:180px;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <select id="statusFilter" class="form-select" style="max-width:150px;">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button class="btn btn-outline-primary" onclick="applyFilters()">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status','category']))
            <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Clear
            </a>
            @endif
        </div>
    </div>

    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $i => $service)
                <tr>
                    <td data-label="#">{{ $services->firstItem() + $i }}</td>
                    <td data-label="Category">
                        @if($service->category)
                        <span class="badge" style="background:#eef2ff;color:#4f46e5;font-size:11px;font-weight:600;">
                            {{ $service->category->name }}
                        </span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td data-label="Title"><div class="fw-600">{{ $service->title }}</div></td>
                    <td data-label="Description">
                        <span class="text-muted" style="font-size:12.5px;">
                            {{ $service->description ? \Str::limit($service->description, 60) : '-' }}
                        </span>
                    </td>
                    <td data-label="Price"><strong>{{ format_currency($service->price ?? 0) }}</strong></td>
                    <td data-label="Status">{!! $service->status_badge !!}</td>
                    <td data-label="Created By">
                        <span class="text-muted" style="font-size:12px;">{{ $service->createdBy?->name ?? '—' }}</span>
                    </td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            @if(auth()->user()->hasPermission('services.edit'))
                            <button onclick="editService({{ $service->id }})" class="act-btn act-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button onclick="toggleStatus({{ $service->id }}, '{{ $service->status }}')"
                                    class="act-btn {{ $service->status === 'active' ? 'act-view' : 'act-edit' }}"
                                    title="{{ $service->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                <i class="bi bi-{{ $service->status === 'active' ? 'pause' : 'play' }}-circle"></i>
                            </button>
                            @endif
                            @if(auth()->user()->hasPermission('services.delete'))
                            <button onclick="deleteService({{ $service->id }})" class="act-btn act-delete" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-grid fs-2 d-block mb-2"></i>No services found
                        @if(auth()->user()->hasPermission('services.create'))
                        <br><button class="btn btn-primary btn-sm mt-2" onclick="openModal()">
                            <i class="bi bi-plus-circle me-1"></i>Add First Service
                        </button>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$services" label="services" />
</div>

{{-- Add / Edit Modal --}}
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f1f5f9;padding:18px 24px;">
                <h5 class="modal-title fw-700" id="serviceModalLabel">
                    <i class="bi bi-tools me-2 text-primary"></i>Add Service
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <input type="hidden" id="serviceId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600">Category</label>
                        <div class="d-flex gap-2">
                            <select id="svcCategory" class="form-select">
                                <option value="">— No Category —</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <a href="{{ route('admin.service-categories.index') }}" target="_blank"
                               class="btn btn-outline-secondary btn-sm px-2" title="Manage Categories">
                                <i class="bi bi-tags"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Status</label>
                        <select id="svcStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Service Title <span class="text-danger">*</span></label>
                        <input type="text" id="svcTitle" class="form-control" placeholder="e.g. Water Tank Cleaning">
                        <div class="text-danger mt-1" id="titleError" style="font-size:12px;"></div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-600">Price</label>
                        <input type="number" id="svcPrice" class="form-control" min="0" step="0.01" placeholder="0.00">
                        <div class="text-danger mt-1" id="priceError" style="font-size:12px;"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Description</label>
                        <textarea id="svcDesc" class="form-control" rows="4"
                                  placeholder="Describe what this service includes…"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:14px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveService()">
                    <i class="bi bi-check-circle me-1"></i>Save Service
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const modal = new bootstrap.Modal(document.getElementById('serviceModal'));
const editServiceUrlTemplate   = '{{ route("admin.services.edit-data", ["service" => "__ID__"]) }}';
const storeServiceUrl         = '{{ route('admin.services.store') }}';
const updateServiceUrlTemplate = '{{ route("admin.services.update", ["service" => "__ID__"]) }}';
const deleteServiceUrlTemplate = '{{ route("admin.services.destroy", ["service" => "__ID__"]) }}';
const toggleServiceUrlTemplate = '{{ route("admin.services.toggle", ["service" => "__ID__"]) }}';

function applyFilters() {
    const params = new URLSearchParams({
        search: $('#searchInput').val(),
        category: $('#categoryFilter').val(),
        status: $('#statusFilter').val()
    });
    window.location.href = `{{ route('admin.services.index') }}?${params.toString()}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyFilters(); });

function openModal() {
    $('#serviceModalLabel').html('<i class="bi bi-tools me-2 text-primary"></i>Add Service');
    $('#serviceId,#svcTitle,#svcDesc,#svcPrice').val('');
    $('#svcCategory').val('');
    $('#svcStatus').val('active');
    clearErrors();
    modal.show();
}

function editService(id) {
    $.get(editServiceUrlTemplate.replace('__ID__', id), function(s) {
        $('#serviceModalLabel').html('<i class="bi bi-pencil me-2 text-warning"></i>Edit Service');
        $('#serviceId').val(s.id);
        $('#svcCategory').val(s.category_id || '');
        $('#svcTitle').val(s.title);
        $('#svcStatus').val(s.status);
        $('#svcPrice').val(s.price ?? '');
        $('#svcDesc').val(s.description || '');
        clearErrors();
        modal.show();
    });
}

function saveService() {
    clearErrors();
    const id   = $('#serviceId').val();
    const url  = id ? updateServiceUrlTemplate.replace('__ID__', id) : storeServiceUrl;
    const type = id ? 'PUT' : 'POST';
    $.ajax({
        url, type,
        data: { category_id: $('#svcCategory').val(), title: $('#svcTitle').val(), price: $('#svcPrice').val(), status: $('#svcStatus').val(), description: $('#svcDesc').val() },
        success: r => {
            modal.hide();
            Swal.fire({ icon:'success', text:r.message, timer:2000, showConfirmButton:false }).then(() => location.reload());
        },
        error: x => {
            const errors = x.responseJSON?.errors || {};
            if (errors.title) { $('#svcTitle').addClass('is-invalid'); $('#titleError').text(errors.title[0]); }
            if (errors.price) { $('#svcPrice').addClass('is-invalid'); $('#priceError').text(errors.price[0]); }
            if (!Object.keys(errors).length) Swal.fire('Error!', x.responseJSON?.message || 'Something went wrong', 'error');
        }
    });
}

function deleteService(id) {
    Swal.fire({ title:'Delete this service?', text:'It will be removed from the list.', icon:'warning',
        showCancelButton:true, confirmButtonColor:'#ef4444', confirmButtonText:'Yes, delete!'
    }).then(r => {
        if (r.isConfirmed) $.ajax({ url: deleteServiceUrlTemplate.replace('__ID__', id), type:'DELETE',
            success: r => Swal.fire('Deleted!', r.message,'success').then(()=>location.reload()),
            error:   x => Swal.fire('Error!', x.responseJSON?.message||'Error','error')
        });
    });
}

function toggleStatus(id, current) {
    Swal.fire({ title: current === 'active' ? 'Deactivate this service?' : 'Activate this service?',
        icon:'question', showCancelButton:true, confirmButtonColor:'#4f46e5', confirmButtonText:'Yes, proceed!'
    }).then(r => {
        if (r.isConfirmed) $.post(toggleServiceUrlTemplate.replace('__ID__', id), {},
            res => Swal.fire({icon:'success', text:res.message, timer:2000, showConfirmButton:false}).then(()=>location.reload())
        ).fail(x => Swal.fire('Error!', x.responseJSON?.message||'Error','error'));
    });
}

function clearErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('#titleError,#priceError').text('');
}
</script>
@endpush
