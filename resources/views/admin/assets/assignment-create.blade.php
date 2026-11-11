@extends('layouts.app')
@section('title', 'New Asset Assignment')

@push('styles')
<style>
.assign-row{display:grid;grid-template-columns:minmax(220px,1fr) 120px 44px;gap:10px;align-items:end;margin-bottom:10px;}
.asset-hint{font-size:11.5px;color:#64748b;margin-top:4px;}
@media(max-width:767px){.assign-row{grid-template-columns:1fr}.assign-row .btn{width:100%;}}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">New Asset Assignment</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.asset-assignments.index') }}">Assign Assets</a></li>
                <li class="breadcrumb-item active">New Assignment</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.asset-assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Assignments</a>
</div>

<div class="card">
    <div class="card-header">
        <div class="fw-800">Assign Assets to Customer Site</div>
        <div class="text-muted" style="font-size:12px;">Employee is optional. Leave it blank when assets are issued directly to the customer site.</div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-600">Employee <span class="text-muted">(optional)</span></label>
                <select id="assignEmployee" class="form-select">
                    <option value="">No employee - customer site only</option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }}{{ $employee->mobile ? ' - '.$employee->mobile : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Customer / Site <span class="text-danger">*</span></label>
                <select id="assignCustomer" class="form-select">
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}{{ $customer->company_name ? ' - '.$customer->company_name : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-600">Issue Date</label>
                <input type="date" id="assignDate" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-600">Reference</label>
                <input type="text" id="assignRef" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label fw-600">Assets <span class="text-danger">*</span></label>
                <div id="assetRows"></div>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAssetRow()"><i class="bi bi-plus-circle me-1"></i>Add Asset Row</button>
            </div>
            <div class="col-12">
                <label class="form-label fw-600">Notes</label>
                <textarea id="assignNotes" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <button class="btn btn-primary" onclick="saveAssignment()"><i class="bi bi-check-circle me-1"></i>Assign Selected Assets</button>
    </div>
</div>
@endsection

@php
    $assetOptionJson = $assets->map(function ($asset) {
        return [
            'id' => $asset->id,
            'name' => $asset->name,
            'code' => $asset->asset_code,
            'available' => $asset->available_quantity,
            'unit' => $asset->unit,
        ];
    })->values();
@endphp

@push('scripts')
<script>
const assetOptions = @json($assetOptionJson);

function assetSelectHtml() {
    return `<select class="form-select asset-select">
        <option value="">Select Asset</option>
        ${assetOptions.map(a => `<option value="${a.id}" data-available="${a.available}" data-unit="${a.unit}">${a.name} (${a.code}) - Available ${a.available} ${a.unit}</option>`).join('')}
    </select><div class="asset-hint">Choose asset from godown stock.</div>`;
}
function addAssetRow() {
    $('#assetRows').append(`<div class="assign-row">
        <div>${assetSelectHtml()}</div>
        <div><label class="form-label fw-600">Qty</label><input type="number" class="form-control asset-qty" min="1" value="1"></div>
        <button type="button" class="btn btn-outline-danger" onclick="$(this).closest('.assign-row').remove()"><i class="bi bi-trash"></i></button>
    </div>`);
}
addAssetRow();

function saveAssignment() {
    const items = [];
    $('.assign-row').each(function() {
        const assetId = $(this).find('.asset-select').val();
        const qty = $(this).find('.asset-qty').val();
        if (assetId && qty) items.push({ asset_item_id: assetId, quantity: qty });
    });
    $.post('{{ route('admin.asset-assignments.store') }}', {
        asset_employee_id: $('#assignEmployee').val(),
        customer_id: $('#assignCustomer').val(),
        issue_date: $('#assignDate').val(),
        reference_no: $('#assignRef').val(),
        notes: $('#assignNotes').val(),
        items
    }).done(r => Swal.fire({icon:'success', text:r.message, timer:1700, showConfirmButton:false}).then(() => window.location.href = '{{ route('admin.asset-assignments.index') }}'))
      .fail(x => Swal.fire('Error', x.responseJSON?.message || Object.values(x.responseJSON?.errors || {})[0]?.[0] || 'Unable to assign assets.', 'error'));
}
</script>
@endpush
