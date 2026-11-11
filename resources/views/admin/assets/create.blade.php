@extends('layouts.app')
@section('title', 'Add Assets')

@push('styles')
<style>
.asset-form-card{border:0;overflow:hidden;}
.asset-form-head{background:linear-gradient(135deg,#eef2ff,#f8fafc);border-bottom:1px solid #e2e8f0;padding:20px 22px;}
.asset-form-title{font-size:17px;font-weight:800;color:#0f172a;}
.asset-form-help{font-size:12.5px;color:#64748b;margin-top:4px;}
.form-actions{display:flex;justify-content:flex-end;gap:10px;padding-top:8px;}
@media(max-width:767px){
    .asset-form-head,.asset-form-card .card-body{padding:16px;}
    .form-actions{flex-direction:column-reverse;}
    .form-actions .btn{width:100%;}
    .page-header .btn{width:100%;}
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Add Assets</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Assets Master</a></li>
                <li class="breadcrumb-item active">Add Assets</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Assets</a>
</div>

<div class="card asset-form-card">
    <div class="asset-form-head">
        <div class="asset-form-title">Add Asset</div>
        <div class="asset-form-help">Enter the asset name and available stock quantity. Asset ID is generated automatically.</div>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-600">Asset Name <span class="text-danger">*</span></label>
                <input id="assetName" class="form-control" placeholder="Safety Belt, Drill Machine">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">Total Quantity <span class="text-danger">*</span></label>
                <input type="number" id="assetTotal" class="form-control" min="1" max="999999" value="1">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Asset ID</label>
                <input id="assetCode" class="form-control" placeholder="Auto generated if blank">
                <div class="form-text">Leave blank to generate automatically.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Category</label>
                <input id="assetCategory" class="form-control" list="assetCategoryList" placeholder="Safety Equipment">
                <datalist id="assetCategoryList">@foreach($categories as $category)<option value="{{ $category }}">@endforeach</datalist>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-600">Unit</label>
                <input id="assetUnit" class="form-control" value="pcs">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">Godown Location</label>
                <input id="assetLocation" class="form-control" placeholder="Main Godown">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">Minimum Alert</label>
                <input type="number" id="assetMinimum" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">Condition</label>
                <select id="assetCondition" class="form-select"><option value="new">New</option><option value="good" selected>Good</option><option value="repair">Repair</option><option value="damaged">Damaged</option></select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">Status</label>
                <select id="assetStatus" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select>
            </div>
            <div class="col-12">
                <label class="form-label fw-600">Notes</label>
                <textarea id="assetNotes" class="form-control" rows="3" placeholder="Any identifying or storage details"></textarea>
            </div>
        </div>
        <div class="form-actions">
            <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary" id="saveAssetButton" onclick="saveAssets()"><i class="bi bi-check-circle me-1"></i>Create Assets</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function saveAssets() {
    $('#saveAssetButton').prop('disabled', true);
    $.post('{{ route('admin.assets.store') }}', {
        asset_code: $('#assetCode').val(),
        name: $('#assetName').val(),
        category: $('#assetCategory').val(),
        unit: $('#assetUnit').val(),
        location: $('#assetLocation').val(),
        total_quantity: $('#assetTotal').val(),
        minimum_quantity: $('#assetMinimum').val(),
        condition: $('#assetCondition').val(),
        status: $('#assetStatus').val(),
        notes: $('#assetNotes').val()
    }).done(r => Swal.fire({icon:'success', text:r.message, timer:1800, showConfirmButton:false}).then(() => window.location.href = '{{ route('admin.assets.index') }}'))
      .fail(x => {
          $('#saveAssetButton').prop('disabled', false);
          Swal.fire('Error', x.responseJSON?.message || Object.values(x.responseJSON?.errors || {})[0]?.[0] || 'Unable to create assets.', 'error');
      });
}
</script>
@endpush
