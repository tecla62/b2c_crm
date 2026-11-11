@extends('layouts.app')
@section('title', 'Assets Master')

@push('styles')
<style>
.asset-stat{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:16px;height:100%;}
.asset-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:5px;}
.asset-value{font-size:22px;font-weight:850;color:#0f172a;}
.asset-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.asset-code,.asset-sub{font-size:11.5px;color:#64748b;margin-top:2px;}
.asset-chip{display:inline-flex;align-items:center;gap:5px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:3px 9px;font-size:11.5px;color:#475569;font-weight:700;}
.stock-grid{display:grid;grid-template-columns:repeat(6,minmax(68px,1fr));gap:7px;min-width:420px;}
.stock-grid div{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:7px;text-align:center;}
.stock-grid strong{display:block;font-size:15px;color:#0f172a;}
.stock-grid span{font-size:10px;font-weight:800;text-transform:uppercase;color:#94a3b8;}
@media(max-width:767px){.stock-grid{min-width:0;grid-template-columns:repeat(2,1fr)}.page-header .btn{width:100%;}.page-header .d-flex{width:100%;}}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Assets Master</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Assets</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.asset-assignments.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left-right me-1"></i>Assign Assets
        </a>
        @if(auth()->user()->hasPermission('assets.create'))
        <a href="{{ route('admin.assets.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Add Assets</a>
        @endif
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach([
        ['Assets', $summary['items'], 'bi-box-seam', '#e0f2fe', '#0284c7'],
        ['Total Stock', $summary['total'], 'bi-stack', '#eef2ff', '#4f46e5'],
        ['Godown Stock', $summary['available'], 'bi-check-circle', '#dcfce7', '#16a34a'],
        ['Issued', $summary['issued'], 'bi-person-check', '#dbeafe', '#1d4ed8'],
        ['Problem Qty', $summary['problem'], 'bi-exclamation-triangle', '#fee2e2', '#dc2626'],
    ] as [$label,$value,$icon,$bg,$color])
    <div class="col-6 col-md">
        <div class="asset-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="asset-label">{{ $label }}</div>
                    <div class="asset-value">{{ $value }}</div>
                </div>
                <div class="asset-icon" style="background:{{ $bg }};color:{{ $color }};"><i class="bi {{ $icon }}"></i></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:230px;" placeholder="Search asset / ID" value="{{ request('search') }}">
            <select id="stockFilter" class="form-select" style="max-width:150px;">
                <option value="">All Stock</option>
                <option value="available" {{ request('stock') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock</option>
                <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Not Available</option>
            </select>
            <select id="categoryFilter" class="form-select" style="max-width:170px;">
                <option value="">All Category</option>
                @foreach($categories as $category)
                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary" onclick="applyAssetFilters()"><i class="bi bi-funnel me-1"></i>Filter</button>
            @if(request()->hasAny(['search','stock','category']))
            <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
            @endif
        </div>
    </div>

    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover">
            <thead>
                <tr><th>#</th><th>Asset Master</th><th>Stock Count</th><th>Location</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                @forelse($assets as $i => $asset)
                <tr>
                    <td data-label="#">{{ $assets->firstItem() + $i }}</td>
                    <td data-label="Asset">
                        <div class="fw-800">{{ $asset->name }}</div>
                        <div class="asset-code">{{ $asset->asset_code }}</div>
                        @if($asset->category)<div class="mt-1"><span class="asset-chip"><i class="bi bi-tag"></i>{{ $asset->category }}</span></div>@endif
                    </td>
                    <td data-label="Stock Count">
                        <div class="stock-grid">
                            <div><strong>{{ $asset->total_quantity }}</strong><span>Total</span></div>
                            <div><strong class="text-success">{{ $asset->available_quantity }}</strong><span>Godown</span></div>
                            <div><strong class="text-primary">{{ $asset->issued_quantity }}</strong><span>Issued</span></div>
                            <div><strong class="text-warning">{{ $asset->maintenance_quantity }}</strong><span>Repair</span></div>
                            <div><strong class="text-danger">{{ $asset->damaged_quantity + $asset->lost_quantity }}</strong><span>Problem</span></div>
                            <div><strong>{{ $asset->minimum_quantity }}</strong><span>Min Alert</span></div>
                        </div>
                    </td>
                    <td data-label="Location"><span class="asset-chip"><i class="bi bi-geo-alt"></i>{{ $asset->location ?: 'Godown' }}</span></td>
                    <td data-label="Status">
                        {!! $asset->stock_badge !!}
                    </td>
                    <td data-label="Action">
                        @if(auth()->user()->hasPermission('assets.edit'))
                        <button onclick="openStockModal({{ $asset->id }}, @js($asset->name), @js($asset->asset_code), {{ $asset->available_quantity }})" class="act-btn act-view" title="Add Stock"><i class="bi bi-plus-square"></i></button>
                        <button onclick="editAsset({{ $asset->id }})" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></button>
                        @endif
                        @if(auth()->user()->hasPermission('assets.delete'))
                        <button onclick="deleteAsset({{ $asset->id }})" class="act-btn act-delete" title="Delete"><i class="bi bi-trash"></i></button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-box-seam fs-2 d-block mb-2"></i>No assets found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$assets" label="assets" />
</div>

<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:14px;border:none;">
        <div class="modal-header"><div><h5 class="modal-title fw-800">Add Stock</h5><div id="stockAssetLabel" class="text-muted" style="font-size:12px;"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="stockAssetId">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-600">Stock In Quantity</label><input type="number" id="stockQty" class="form-control" min="1" value="1"></div>
                <div class="col-md-6"><label class="form-label fw-600">Stock Date</label><input type="date" id="stockDate" class="form-control" value="{{ now()->format('Y-m-d') }}"></div>
                <div class="col-12"><label class="form-label fw-600">Reference</label><input type="text" id="stockRef" class="form-control" placeholder="Purchase / challan reference"></div>
                <div class="col-12"><label class="form-label fw-600">Notes</label><textarea id="stockNotes" class="form-control" rows="2"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" onclick="saveStock()">Add Stock</button></div>
    </div></div>
</div>

<div class="modal fade" id="assetModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:14px;border:none;">
        <div class="modal-header"><h5 class="modal-title fw-800" id="assetModalLabel">Edit Asset</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="assetId">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-600">Asset ID</label><input id="assetCode" class="form-control" placeholder="Asset ID"></div>
                <div class="col-md-8"><label class="form-label fw-600">Asset Name *</label><input id="assetName" class="form-control" placeholder="Drill Machine, Safety Belt"></div>
                <div class="col-md-4"><label class="form-label fw-600">Category</label><input id="assetCategory" class="form-control" list="assetCategoryList"><datalist id="assetCategoryList">@foreach($categories as $category)<option value="{{ $category }}">@endforeach</datalist></div>
                <div class="col-md-4"><label class="form-label fw-600">Unit</label><input id="assetUnit" class="form-control" value="pcs"></div>
                <div class="col-md-4"><label class="form-label fw-600">Godown Location</label><input id="assetLocation" class="form-control" placeholder="Main Godown"></div>
                <div class="col-md-4"><label class="form-label fw-600">Total Quantity</label><input type="number" id="assetTotal" class="form-control" min="0" value="0"></div>
                <div class="col-md-4"><label class="form-label fw-600">Minimum Alert</label><input type="number" id="assetMinimum" class="form-control" min="0" value="0"></div>
                <div class="col-md-4"><label class="form-label fw-600">Condition</label><select id="assetCondition" class="form-select"><option value="new">New</option><option value="good" selected>Good</option><option value="repair">Repair</option><option value="damaged">Damaged</option></select></div>
                <div class="col-md-4"><label class="form-label fw-600">Status</label><select id="assetStatus" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="col-12"><label class="form-label fw-600">Notes</label><textarea id="assetNotes" class="form-control" rows="3"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" onclick="saveAsset()">Save Asset</button></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
const assetModal = new bootstrap.Modal(document.getElementById('assetModal'));
const stockModal = new bootstrap.Modal(document.getElementById('stockModal'));

function applyAssetFilters() {
    const params = new URLSearchParams({ search: $('#searchInput').val(), stock: $('#stockFilter').val(), category: $('#categoryFilter').val() });
    [...params.entries()].forEach(([k,v]) => { if (!v) params.delete(k); });
    window.location.href = `{{ route('admin.assets.index') }}?${params.toString()}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyAssetFilters(); });

const assetEditDataUrl = id => '{{ route("admin.assets.edit-data", ["assetItem" => "__ASSET__"]) }}'.replace('__ASSET__', id);
const assetMovementUrl = id => '{{ route("admin.assets.movement", ["assetItem" => "__ASSET__"]) }}'.replace('__ASSET__', id);
const assetUpdateUrl = id => '{{ route("admin.assets.update", ["assetItem" => "__ASSET__"]) }}'.replace('__ASSET__', id);
const assetDeleteUrl = id => '{{ route("admin.assets.destroy", ["assetItem" => "__ASSET__"]) }}'.replace('__ASSET__', id);

function editAsset(id){ $.get(assetEditDataUrl(id), a => { $('#assetModalLabel').text('Edit Asset'); $('#assetId').val(a.id); $('#assetCode').val(a.asset_code); $('#assetName').val(a.name); $('#assetCategory').val(a.category || ''); $('#assetUnit').val(a.unit || 'pcs'); $('#assetLocation').val(a.location || ''); $('#assetTotal').val(a.total_quantity); $('#assetMinimum').val(a.minimum_quantity); $('#assetCondition').val(a.condition); $('#assetStatus').val(a.status); $('#assetNotes').val(a.notes || ''); assetModal.show(); }); }
function openStockModal(id, name, code, available) {
    $('#stockAssetId').val(id);
    $('#stockAssetLabel').text(`${name} (${code}) - Current available: ${available}`);
    $('#stockQty').val(1);
    $('#stockRef,#stockNotes').val('');
    stockModal.show();
}
function saveStock() {
    $.post(assetMovementUrl($('#stockAssetId').val()), {
        type: 'stock_in',
        quantity: $('#stockQty').val(),
        movement_date: $('#stockDate').val(),
        reference_no: $('#stockRef').val(),
        notes: $('#stockNotes').val()
    }).done(r => Swal.fire({icon:'success', text:r.message, timer:1600, showConfirmButton:false}).then(() => location.reload()))
      .fail(x => Swal.fire('Error', x.responseJSON?.message || Object.values(x.responseJSON?.errors || {})[0]?.[0] || 'Unable to add stock.', 'error'));
}
function saveAsset(){
    const id = $('#assetId').val();
    $.ajax({ url: assetUpdateUrl(id), type: 'PUT', data:{ asset_code:$('#assetCode').val(), name:$('#assetName').val(), category:$('#assetCategory').val(), unit:$('#assetUnit').val(), location:$('#assetLocation').val(), total_quantity:$('#assetTotal').val(), minimum_quantity:$('#assetMinimum').val(), condition:$('#assetCondition').val(), status:$('#assetStatus').val(), notes:$('#assetNotes').val() }})
    .done(r => Swal.fire({icon:'success', text:r.message, timer:1600, showConfirmButton:false}).then(()=>location.reload()))
    .fail(x => Swal.fire('Error', x.responseJSON?.message || Object.values(x.responseJSON?.errors || {})[0]?.[0] || 'Unable to save asset.', 'error'));
}
function deleteAsset(id){
    Swal.fire({title:'Delete this asset?', icon:'warning', showCancelButton:true, confirmButtonColor:'#ef4444'}).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({url: assetDeleteUrl(id), type:'DELETE'}).done(r => Swal.fire('Deleted', r.message, 'success').then(()=>location.reload())).fail(x => Swal.fire('Error', x.responseJSON?.message || 'Error', 'error'));
    });
}
</script>
@endpush
