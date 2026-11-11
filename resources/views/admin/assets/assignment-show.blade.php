@extends('layouts.app')
@section('title', 'Assignment Details')

@push('styles')
<style>
.assign-summary{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:16px;height:100%;}
.summary-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:4px;}
.summary-value{font-size:18px;font-weight:800;color:#0f172a;}
.group-badge{display:inline-flex;align-items:center;gap:6px;background:#eef2ff;border-radius:999px;padding:6px 13px;color:#4338ca;font-weight:800;}
.unit-id{font-size:12px;color:#4338ca;font-weight:800;}
@media(max-width:767px){.page-header .btn{width:100%;}.action-stack{display:flex;gap:8px;}}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Assignment Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.asset-assignments.index') }}">Assign Assets</a></li>
                <li class="breadcrumb-item active">{{ $groupReference }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($pendingQuantity > 0 && auth()->user()->hasPermission('assets.edit'))
        <button class="btn btn-success" onclick="openGroupReturn()"><i class="bi bi-arrow-return-left me-1"></i>Return All</button>
        @endif
        <a href="{{ route('admin.asset-assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div class="fw-800 fs-5">{{ $assignment->customer?->name ?? 'Customer Site' }}</div>
            <div class="text-muted">{{ $assignment->employee?->name ?? 'Assigned directly to site' }} &middot; {{ $assignment->issue_date->format('d M Y') }}</div>
        </div>
        <span class="group-badge"><i class="bi bi-receipt"></i>{{ $groupReference }}</span>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach([
        ['Issued Assets', $assignments->sum('quantity')],
        ['Pending Return', $pendingQuantity],
        ['Returned', $assignments->sum('returned_quantity')],
    ] as [$label, $value])
    <div class="col-6 col-md-4"><div class="assign-summary"><div class="summary-label">{{ $label }}</div><div class="summary-value">{{ $value }}</div></div></div>
    @endforeach
</div>

<div class="card">
    <div class="card-header"><div class="fw-800">Assets in this Assignment</div></div>
    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover">
            <thead><tr><th>#</th><th>Asset</th><th>Qty</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @foreach($assignments as $index => $issuedAsset)
                <tr>
                    <td data-label="#">{{ $index + 1 }}</td>
                    <td data-label="Asset">
                        <div class="fw-800">{{ $issuedAsset->assetItem?->name }}</div>
                        <div class="unit-id">{{ $issuedAsset->assetItem?->asset_code }}</div>
                    </td>
                    <td data-label="Qty">Pending {{ $issuedAsset->balance_quantity }} / Issued {{ $issuedAsset->quantity }}</td>
                    <td data-label="Status">{!! $issuedAsset->status_badge !!}</td>
                    <td data-label="Action">
                        @if($issuedAsset->balance_quantity > 0 && auth()->user()->hasPermission('assets.edit'))
                        <button class="btn btn-sm btn-outline-success" onclick="openSingleReturn({{ $issuedAsset->id }}, @js($issuedAsset->assetItem?->asset_code), {{ $issuedAsset->balance_quantity }})">
                            Return This Asset
                        </button>
                        @elseif($issuedAsset->balance_quantity <= 0)
                        <span class="text-muted">Returned</span>
                        @else
                        <span class="text-muted">View only</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="detailReturnModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:14px;border:none;">
        <div class="modal-header"><div><h5 class="modal-title fw-800" id="returnTitle">Return Asset</h5><div class="text-muted" id="returnDescription" style="font-size:12px;"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="returnTarget">
            <input type="hidden" id="returnIsGroup">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-600">Return Quantity</label><input type="number" id="returnQty" class="form-control" min="1" value="1"></div>
                <div class="col-md-6"><label class="form-label fw-600">Return Date</label><input type="date" id="returnDate" class="form-control" value="{{ now()->format('Y-m-d') }}"></div>
                <div class="col-12"><label class="form-label fw-600">Notes</label><textarea id="returnNotes" class="form-control" rows="3"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" onclick="saveReturn()">Confirm Return</button></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
const detailReturnModal = new bootstrap.Modal(document.getElementById('detailReturnModal'));

function openSingleReturn(id, assetId, balance) {
    $('#returnTarget').val(id);
    $('#returnIsGroup').val('0');
    $('#returnTitle').text('Return Asset');
    $('#returnDescription').text(assetId);
    $('#returnQty').val(balance).attr('max', balance).prop('readonly', false);
    $('#returnNotes').val('');
    detailReturnModal.show();
}
function openGroupReturn() {
    $('#returnTarget').val(@js($assignment->assignment_group_code));
    $('#returnIsGroup').val('1');
    $('#returnTitle').text('Return All Assets');
    $('#returnDescription').text('{{ $groupReference }} - {{ $pendingQuantity }} pending assets');
    $('#returnQty').val({{ $pendingQuantity }}).attr('max', {{ $pendingQuantity }}).prop('readonly', true);
    $('#returnNotes').val('');
    detailReturnModal.show();
}
function saveReturn() {
    const isGroup = $('#returnIsGroup').val() === '1';
    const assetAssignmentReturnUrl = id => '{{ route("admin.asset-assignments.return", ["assetAssignment" => "__ASSIGN__"]) }}'.replace('__ASSIGN__', id);
    const assetGroupReturnUrl = id => '{{ route("admin.asset-assignment-groups.return", ["assignmentGroup" => "__GROUP__"]) }}'.replace('__GROUP__', id);
    const url = isGroup
        ? assetGroupReturnUrl($('#returnTarget').val())
        : assetAssignmentReturnUrl($('#returnTarget').val());
    $.post(url, {
        quantity: $('#returnQty').val(),
        return_date: $('#returnDate').val(),
        notes: $('#returnNotes').val()
    }).done(r => Swal.fire({icon:'success', text:r.message, timer:1600, showConfirmButton:false}).then(() => location.reload()))
      .fail(x => Swal.fire('Error', x.responseJSON?.message || Object.values(x.responseJSON?.errors || {})[0]?.[0] || 'Unable to return asset.', 'error'));
}
</script>
@endpush
