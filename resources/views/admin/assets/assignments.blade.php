@extends('layouts.app')
@section('title', 'Assign Assets')

@push('styles')
<style>
.assign-stat{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:16px;height:100%;}
.assign-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:5px;}
.assign-value{font-size:22px;font-weight:850;color:#0f172a;}
.assign-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.assign-chip{display:inline-flex;align-items:center;gap:5px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:3px 9px;font-size:11.5px;color:#475569;font-weight:700;}
.assignment-ref{display:inline-flex;align-items:center;gap:5px;background:#eef2ff;border-radius:999px;padding:3px 10px;color:#4338ca;font-size:11.5px;font-weight:800;text-decoration:none;margin-top:6px;}
.assignment-ref:hover{background:#e0e7ff;color:#3730a3;}
@media(max-width:767px){.page-header .btn{width:100%;}.page-header .d-flex{width:100%;}}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Assign Assets</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Assets Master</a></li>
                <li class="breadcrumb-item active">Assign Assets</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(auth()->user()->hasPermission('assets.edit'))
        <a href="{{ route('admin.asset-assignments.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>New Assignment</a>
        @endif
        @if(auth()->user()->hasPermission('assets.create'))
        <button class="btn btn-outline-primary" onclick="openEmployeeModal()"><i class="bi bi-person-plus me-1"></i>Add Employee</button>
        @endif
        <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-primary"><i class="bi bi-box-seam me-1"></i>Assets Master</a>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach([
        ['Open Issues', $summary['open'], 'bi-arrow-left-right', '#fef3c7', '#d97706'],
        ['Pending Qty', $summary['pending_qty'], 'bi-box-arrow-up-right', '#dbeafe', '#1d4ed8'],
        ['Employees', $summary['employees'], 'bi-people', '#eef2ff', '#4f46e5'],
        ['Customers', $summary['customers'], 'bi-person-badge', '#e0f2fe', '#0284c7'],
    ] as [$label,$value,$icon,$bg,$color])
    <div class="col-6 col-md-3">
        <div class="assign-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="assign-label">{{ $label }}</div>
                    <div class="assign-value">{{ $value }}</div>
                </div>
                <div class="assign-icon" style="background:{{ $bg }};color:{{ $color }};"><i class="bi {{ $icon }}"></i></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:230px;" placeholder="Search asset / employee / customer" value="{{ request('search') }}">
            <select id="employeeFilter" class="form-select" style="max-width:210px;">
                <option value="">All Employees</option>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                @endforeach
            </select>
            <select id="customerFilter" class="form-select" style="max-width:230px;">
                <option value="">All Customers</option>
                @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary" onclick="applyAssignmentFilters()"><i class="bi bi-funnel me-1"></i>Filter</button>
            @if(request()->hasAny(['search','employee_id','customer_id']))
            <a href="{{ route('admin.asset-assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
            @endif
        </div>
    </div>
    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover">
            <thead>
                <tr><th>#</th><th>Asset</th><th>Employee</th><th>Customer / Site</th><th>Qty</th><th>Issue Date</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                @forelse($assignments as $i => $assignmentGroup)
                @php
                    $assignment = $assignmentGroup->first();
                    $issuedQuantity = $assignmentGroup->sum('quantity');
                    $pendingQuantity = $assignmentGroup->sum(fn($item) => $item->balance_quantity);
                    $isGrouped = filled($assignment->assignment_group_code);
                    $assetNames = $assignmentGroup->pluck('assetItem.name')->filter()->unique()->join(', ');
                    $groupReference = $isGrouped ? 'ASN-' . strtoupper(substr(str_replace('-', '', $assignment->assignment_group_code), 0, 10)) : $assignment->assetItem?->asset_code;
                @endphp
                <tr>
                    <td data-label="#">{{ $assignments->firstItem() + $i }}</td>
                    <td data-label="Asset">
                        <div class="fw-800">{{ $assetNames }}</div>
                        @if($isGrouped)
                        <a class="assignment-ref" href="{{ route('admin.asset-assignment-groups.show', $assignment->assignment_group_code) }}"><i class="bi bi-receipt"></i>{{ $groupReference }}</a>
                        @else
                        <div class="text-muted" style="font-size:11.5px;">{{ $groupReference }}</div>
                        @endif
                    </td>
                    <td data-label="Employee">{{ $assignment->employee?->name ?? 'Site assignment' }}</td>
                    <td data-label="Customer / Site">{{ $assignment->customer?->name ?? '-' }}</td>
                    <td data-label="Qty">
                        <span class="assign-chip">Issued {{ $issuedQuantity }}</span>
                        <span class="assign-chip">Pending {{ $pendingQuantity }}</span>
                    </td>
                    <td data-label="Issue Date">{{ $assignment->issue_date->format('d M Y') }}</td>
                    <td data-label="Status">{!! $assignment->status_badge !!}</td>
                    <td data-label="Action">
                        @if($isGrouped)
                        <a class="act-btn act-view" title="View / Return Assets" href="{{ route('admin.asset-assignment-groups.show', $assignment->assignment_group_code) }}"><i class="bi bi-eye"></i></a>
                        @elseif(auth()->user()->hasPermission('assets.edit'))
                        <button class="act-btn act-view" title="Return" onclick="openReturnModal(@js($isGrouped ? $assignment->assignment_group_code : (string) $assignment->id), @js($assetNames), {{ $pendingQuantity }}, {{ $isGrouped ? 'true' : 'false' }})">
                            <i class="bi bi-arrow-return-left"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No assigned assets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$assignments" label="assignments" />
</div>

<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:14px;border:none;">
        <div class="modal-header"><h5 class="modal-title fw-800">Add Employee</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label fw-600">Employee Code</label><input id="empCode" class="form-control" placeholder="Auto generated if blank"><div class="form-text">Example: EMP-{{ now()->format('Y') }}-0001</div></div>
                <div class="col-md-7"><label class="form-label fw-600">Employee Name *</label><input id="empName" class="form-control"></div>
                <div class="col-md-6"><label class="form-label fw-600">Mobile</label><input id="empMobile" class="form-control"></div>
                <div class="col-md-6"><label class="form-label fw-600">Designation</label><input id="empDesignation" class="form-control"></div>
                <div class="col-12"><label class="form-label fw-600">Notes</label><textarea id="empNotes" class="form-control" rows="2"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" onclick="saveEmployee()">Save Employee</button></div>
    </div></div>
</div>

<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:14px;border:none;">
        <div class="modal-header"><div><h5 class="modal-title fw-800">Return Asset</h5><div class="text-muted" id="returnAssetName" style="font-size:12px;"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="returnAssignmentId">
            <input type="hidden" id="returnIsGroup">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-600">Return Quantity</label><input type="number" id="returnQty" class="form-control" min="1" value="1"></div>
                <div class="col-md-6"><label class="form-label fw-600">Return Date</label><input type="date" id="returnDate" class="form-control" value="{{ now()->format('Y-m-d') }}"></div>
                <div class="col-12"><label class="form-label fw-600">Notes</label><textarea id="returnNotes" class="form-control" rows="3"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" onclick="saveReturn()">Return to Godown</button></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
const employeeModal = new bootstrap.Modal(document.getElementById('employeeModal'));
const returnModal = new bootstrap.Modal(document.getElementById('returnModal'));

function applyAssignmentFilters() {
    const params = new URLSearchParams({ search: $('#searchInput').val(), employee_id: $('#employeeFilter').val(), customer_id: $('#customerFilter').val() });
    [...params.entries()].forEach(([k,v]) => { if (!v) params.delete(k); });
    window.location.href = `{{ route('admin.asset-assignments.index') }}?${params.toString()}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyAssignmentFilters(); });

function openEmployeeModal(){ $('#empCode,#empName,#empMobile,#empDesignation,#empNotes').val(''); employeeModal.show(); }
function saveEmployee(){
    $.post('{{ route('admin.assets.employees.store') }}', { employee_code:$('#empCode').val(), name:$('#empName').val(), mobile:$('#empMobile').val(), designation:$('#empDesignation').val(), status:'active', notes:$('#empNotes').val() })
    .done(r => Swal.fire({icon:'success', text:r.message, timer:1600, showConfirmButton:false}).then(()=>location.reload()))
    .fail(x => Swal.fire('Error', x.responseJSON?.message || Object.values(x.responseJSON?.errors || {})[0]?.[0] || 'Unable to save employee.', 'error'));
}

function openReturnModal(id, assetName, balance, isGroup) {
    $('#returnAssignmentId').val(id);
    $('#returnIsGroup').val(isGroup ? '1' : '0');
    $('#returnAssetName').text(isGroup ? `${assetName} - all ${balance} pending assets` : assetName);
    $('#returnQty').val(balance).attr('max', balance).prop('readonly', isGroup);
    $('#returnNotes').val('');
    returnModal.show();
}
function saveReturn() {
    const isGroup = $('#returnIsGroup').val() === '1';
    const assetAssignmentReturnUrl = id => '{{ route("admin.asset-assignments.return", ["assetAssignment" => "__ASSIGN__"]) }}'.replace('__ASSIGN__', id);
    const assetGroupReturnUrl = id => '{{ route("admin.asset-assignment-groups.return", ["assignmentGroup" => "__GROUP__"]) }}'.replace('__GROUP__', id);
    const url = isGroup
        ? assetGroupReturnUrl($('#returnAssignmentId').val())
        : assetAssignmentReturnUrl($('#returnAssignmentId').val());
    $.post(url, {
        quantity: $('#returnQty').val(),
        return_date: $('#returnDate').val(),
        notes: $('#returnNotes').val()
    }).done(r => Swal.fire({icon:'success', text:r.message, timer:1600, showConfirmButton:false}).then(()=>location.reload()))
      .fail(x => Swal.fire('Error', x.responseJSON?.message || Object.values(x.responseJSON?.errors || {})[0]?.[0] || 'Unable to return asset.', 'error'));
}
</script>
@endpush
