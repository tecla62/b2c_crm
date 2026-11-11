@extends('layouts.app')
@section('title', 'Service Categories')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Service Categories</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Services</a></li>
                <li class="breadcrumb-item active">Categories</li>
            </ol>
        </nav>
    </div>
    @if(auth()->user()->hasPermission('services.create'))
    <button class="btn btn-primary" onclick="openModal()">
        <i class="bi bi-plus-circle me-2"></i>Add Category
    </button>
    @endif
</div>

{{-- Stats --}}
@php
    $total    = \App\Models\ServiceCategory::count();
    $active   = \App\Models\ServiceCategory::where('status','active')->count();
    $inactive = \App\Models\ServiceCategory::where('status','inactive')->count();
@endphp
<div class="row g-3 mb-4">
    @foreach([
        ['Total',    $total,    'bi-tags-fill',        '#eef2ff','#4f46e5'],
        ['Active',   $active,   'bi-check-circle-fill','#f0fdf4','#16a34a'],
        ['Inactive', $inactive, 'bi-pause-circle-fill','#f8fafc','#94a3b8'],
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
                   placeholder="Search categories…" value="{{ request('search') }}">
            <select id="statusFilter" class="form-select" style="max-width:150px;">
                <option value="">All Status</option>
                <option value="active"   {{ request('status') == 'active'   ? 'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected':'' }}>Inactive</option>
            </select>
            <button class="btn btn-outline-primary" onclick="applyFilters()">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-secondary">
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
                    <th>Category Name</th>
                    <th>Services Count</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $i => $cat)
                <tr>
                    <td data-label="#">{{ $categories->firstItem() + $i }}</td>
                    <td data-label="Category Name">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;border-radius:8px;background:#eef2ff;color:#4f46e5;
                                        display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">
                                <i class="bi bi-tag-fill"></i>
                            </div>
                            <div class="fw-600">{{ $cat->name }}</div>
                        </div>
                    </td>
                    <td data-label="Services">
                        <span class="badge bg-light text-dark fw-600" style="font-size:12px;">
                            {{ $cat->services_count }} service{{ $cat->services_count != 1 ? 's' : '' }}
                        </span>
                    </td>
                    <td data-label="Status">
                        @if($cat->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td data-label="Created By">
                        <span class="text-muted" style="font-size:12px;">{{ $cat->createdBy?->name ?? '—' }}</span>
                    </td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            @if(auth()->user()->hasPermission('services.edit'))
                            <button onclick="editCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ $cat->status }}')"
                                    class="act-btn act-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @endif
                            @if(auth()->user()->hasPermission('services.delete'))
                            <button onclick="deleteCategory({{ $cat->id }})" class="act-btn act-delete" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-tags fs-2 d-block mb-2"></i>No categories found
                        @if(auth()->user()->hasPermission('services.create'))
                        <br><button class="btn btn-primary btn-sm mt-2" onclick="openModal()">
                            <i class="bi bi-plus-circle me-1"></i>Add First Category
                        </button>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$categories" label="categories" />
</div>

{{-- Add / Edit Modal --}}
<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f1f5f9;padding:18px 24px;">
                <h5 class="modal-title fw-700" id="catModalLabel">
                    <i class="bi bi-tags me-2 text-primary"></i>Add Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <input type="hidden" id="catId">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-600">Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="catName" class="form-control" placeholder="e.g. Cleaning Services">
                        <div class="text-danger mt-1" id="catNameError" style="font-size:12px;"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Status</label>
                        <select id="catStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:14px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCategory()">
                    <i class="bi bi-check-circle me-1"></i>Save Category
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const modal = new bootstrap.Modal(document.getElementById('catModal'));

function applyFilters() {
    window.location.href = `{{ route('admin.service-categories.index') }}?search=${$('#searchInput').val()}&status=${$('#statusFilter').val()}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyFilters(); });

function openModal() {
    $('#catModalLabel').html('<i class="bi bi-tags me-2 text-primary"></i>Add Category');
    $('#catId,#catName').val('');
    $('#catStatus').val('active');
    $('#catNameError').text('');
    $('#catName').removeClass('is-invalid');
    modal.show();
}

function editCategory(id, name, status) {
    $('#catModalLabel').html('<i class="bi bi-pencil me-2 text-warning"></i>Edit Category');
    $('#catId').val(id);
    $('#catName').val(name);
    $('#catStatus').val(status);
    $('#catNameError').text('');
    $('#catName').removeClass('is-invalid');
    modal.show();
}

const storeCategoryUrl = '{{ route('admin.service-categories.store') }}';
const updateCategoryUrlTemplate = '{{ route("admin.service-categories.update", ["serviceCategory" => "__ID__"]) }}';
const deleteCategoryUrlTemplate = '{{ route("admin.service-categories.destroy", ["serviceCategory" => "__ID__"]) }}';

function saveCategory() {
    $('#catName').removeClass('is-invalid');
    $('#catNameError').text('');
    const id   = $('#catId').val();
    const url  = id ? updateCategoryUrlTemplate.replace('__ID__', id) : storeCategoryUrl;
    const type = id ? 'PUT' : 'POST';
    $.ajax({
        url, type,
        data: { name: $('#catName').val(), status: $('#catStatus').val() },
        success: r => {
            modal.hide();
            Swal.fire({ icon:'success', text:r.message, timer:2000, showConfirmButton:false }).then(() => location.reload());
        },
        error: x => {
            const errors = x.responseJSON?.errors || {};
            if (errors.name) { $('#catName').addClass('is-invalid'); $('#catNameError').text(errors.name[0]); }
            if (!Object.keys(errors).length) Swal.fire('Error!', x.responseJSON?.message || 'Something went wrong', 'error');
        }
    });
}

function deleteCategory(id) {
    Swal.fire({ title:'Delete this category?', text:'Services in this category will be unlinked.',
        icon:'warning', showCancelButton:true, confirmButtonColor:'#ef4444', confirmButtonText:'Yes, delete!'
    }).then(r => {
        if (r.isConfirmed) $.ajax({ url: deleteCategoryUrlTemplate.replace('__ID__', id), type:'DELETE',
            success: r => Swal.fire('Deleted!', r.message,'success').then(()=>location.reload()),
            error:   x => Swal.fire('Error!', x.responseJSON?.message||'Error','error')
        });
    });
}
</script>
@endpush
