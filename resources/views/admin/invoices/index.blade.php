@extends('layouts.app')
@section('title','Invoices')

@push('styles')
<style>
.preview-iframe { width:100%; height:75vh; border:none; }
.act-btn.act-download { background:#f0fdf4; color:#16a34a; }
.act-btn.act-download:hover { background:#16a34a; color:#fff; }
.act-btn.act-preview { background:#fef3c7; color:#d97706; }
.act-btn.act-preview:hover { background:#d97706; color:#fff; }
@media(max-width:767px) {
    .action-btns { flex-wrap:wrap; }
    .preview-iframe { height:60vh; }
    #previewModal .modal-dialog { margin:8px; max-width:calc(100% - 16px); }
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Invoices</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Invoices</li>
            </ol>
        </nav>
    </div>
    @if(auth()->user()->hasPermission('invoices.create'))
    <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Invoice
    </a>
    @endif
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:280px;"
                   placeholder="Search invoice / quotation / customer…" value="{{ request('search') }}">
            <select id="statusFilter" class="form-select" style="max-width:150px;">
                <option value="">All Status</option>
                @foreach(['draft','sent','paid','partial','overdue','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary" onclick="applyFilters()">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Clear
            </a>
            @endif
        </div>
    </div>

    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice / Quotation</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Amount & Discounts</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $i => $inv)
                <tr>
                    <td data-label="#">{{ $invoices->firstItem() + $i }}</td>
                    <td data-label="Invoice / Quotation">
                        <a href="{{ route('admin.invoices.show', $inv) }}" class="fw-700 text-primary text-decoration-none d-block">
                            {{ $inv->invoice_number }}
                        </a>
                        @if($inv->quotation)
                        <div class="mt-1">
                            <a href="{{ route('admin.quotations.show', $inv->quotation) }}"
                               class="badge bg-light text-dark border text-decoration-none"
                               title="Linked Quotation #{{ $inv->quotation->quotation_number }}"
                               style="font-size:11px;font-weight:500;">
                                <i class="bi bi-file-earmark-text text-primary me-1"></i>{{ $inv->quotation->quotation_number }}
                            </a>
                        </div>
                        @else
                        <span class="text-muted" style="font-size:11px;">Direct Invoice</span>
                        @endif
                    </td>
                    <td data-label="Customer">
                        <div class="fw-600">{{ $inv->customer?->name ?? 'N/A' }}</div>
                        <div class="text-muted" style="font-size:11.5px;">{{ $inv->customer?->company_name ?? '' }}</div>
                    </td>
                    <td data-label="Date">{{ $inv->date->format('d M Y') }}</td>
                    <td data-label="Due Date">
                        @if($inv->due_date)
                            <span class="{{ $inv->due_date->isPast() && !in_array($inv->status,['paid','cancelled']) ? 'text-danger fw-600' : '' }}">
                                {{ $inv->due_date->format('d M Y') }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td data-label="Amount & Discounts">
                        <div class="fw-700 fs-6 text-dark">{{ format_currency($inv->total) }}</div>
                        @if($inv->quotation)
                            @php
                                $quotTotal = (float)$inv->quotation->total;
                                $invTotal  = (float)$inv->total;
                                $diff      = $quotTotal - $invTotal;
                                $directDisc = (float)($inv->discount ?? 0);
                            @endphp
                            <div class="d-flex flex-column gap-1 mt-1" style="font-size:11.5px;line-height:1.2;">
                                <div class="text-muted d-flex align-items-center gap-1">
                                    <span>Quotation:</span>
                                    <span class="text-decoration-line-through text-secondary">{{ format_currency($quotTotal) }}</span>
                                </div>
                                @if($directDisc > 0)
                                <div class="text-danger fw-600">
                                    <i class="bi bi-tag-fill me-1" style="font-size:10px;"></i>Disc: −{{ format_currency($directDisc) }}
                                    @if($inv->discount_percent > 0)
                                        <span class="text-muted fw-normal">({{ $inv->discount_percent }}%)</span>
                                    @endif
                                </div>
                                @elseif($diff > 0.01)
                                <div class="text-success fw-600">
                                    <i class="bi bi-arrow-down-circle me-1" style="font-size:10px;"></i>Reduced: −{{ format_currency($diff) }}
                                </div>
                                @endif
                            </div>
                        @elseif((float)($inv->discount ?? 0) > 0)
                            <div class="text-danger mt-1" style="font-size:11.5px;">
                                <i class="bi bi-tag-fill me-1" style="font-size:10px;"></i>Disc: −{{ format_currency($inv->discount) }}
                                @if($inv->discount_percent > 0)
                                    <span class="text-muted">({{ $inv->discount_percent }}%)</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td data-label="Status" id="status-badge-{{ $inv->id }}">{!! $inv->status_badge !!}</td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            <a href="{{ route('admin.invoices.show', $inv) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            <button type="button" onclick="downloadPdf('{{ $inv->id }}')" class="act-btn act-download" title="Download PDF"><i class="bi bi-download"></i></button>
                            <button type="button" onclick="previewInvoice('{{ $inv->id }}')" class="act-btn act-preview" title="Preview"><i class="bi bi-file-earmark-text"></i></button>
                            @if(auth()->user()->hasPermission('invoices.edit'))
                            <a href="{{ route('admin.invoices.edit', $inv) }}" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                            <div class="dropdown d-inline-block">
                                <button class="act-btn" title="Change Status" data-bs-toggle="dropdown" style="background:#f0f4ff;color:var(--primary,#4f46e5);">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="min-width:140px;font-size:13px;">
                                    @foreach(['draft','sent','paid','partial','overdue','cancelled'] as $s)
                                    <li>
                                        <a class="dropdown-item {{ $inv->status === $s ? 'fw-700 text-primary' : '' }}"
                                           href="#"
                                           onclick="changeInvoiceStatus({{ $inv->id }},'{{ $s }}');return false;">
                                            {{ $inv->status === $s ? '✓ ' : '' }}{{ ucfirst($s) }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @if(auth()->user()->hasPermission('invoices.delete'))
                            <button onclick="deleteInvoice({{ $inv->id }})" class="act-btn act-delete" title="Delete"><i class="bi bi-trash"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-receipt fs-2 d-block mb-2"></i>No invoices found
                        @if(auth()->user()->hasPermission('invoices.create'))
                        <br><a href="{{ route('admin.invoices.create') }}" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-plus-circle me-1"></i>Create First Invoice
                        </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$invoices" label="invoices" />
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;overflow:hidden;">
            <div class="modal-header py-2 px-3">
                <h6 class="modal-title fw-700"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Invoice Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="previewIframe" class="preview-iframe"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    window.location.href = `{{ route('admin.invoices.index') }}?search=${$('#searchInput').val()}&status=${$('#statusFilter').val()}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyFilters(); });

function changeInvoiceStatus(id, status) {
    if (status === 'sent') {
        askInvoiceDescThen(showDesc => postInvoiceStatus(id, status, showDesc));
        return;
    }

    postInvoiceStatus(id, status);
}

function askInvoiceDescThen(callback) {
    Swal.fire({
        title: 'Send invoice email',
        text: 'Include service descriptions in the customer PDF?',
        html: `<p class="text-muted small mb-3">Choose how the invoice PDF should be attached.</p>
               <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                 <button type="button" id="btnWithDesc" class="btn btn-primary px-4 py-3 flex-fill">
                   <i class="bi bi-card-text d-block fs-4 mb-1"></i>With Description
                 </button>
                 <button type="button" id="btnNoDesc" class="btn btn-outline-secondary px-4 py-3 flex-fill">
                   <i class="bi bi-list-ul d-block fs-4 mb-1"></i>Without Description
                 </button>
               </div>`,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Cancel',
        didOpen: () => {
            document.getElementById('btnWithDesc').onclick = () => { Swal.close(); callback(1); };
            document.getElementById('btnNoDesc').onclick = () => { Swal.close(); callback(0); };
        }
    });
}

function postInvoiceStatus(id, status, showDesc = null) {
    const payload = { status };
    if (showDesc !== null) payload.show_desc = showDesc;

    $.post('{{ route('admin.invoices.status', '__ID__') }}'.replace('__ID__', id), payload,
        r => {
            document.getElementById(`status-badge-${id}`).innerHTML = r.badge;
            Swal.fire({ icon:'success', text: r.message, timer:1500, showConfirmButton:false });
        }
    ).fail(x => Swal.fire('Error', x.responseJSON?.message || 'Error', 'error'));
}

function deleteInvoice(id) {
    Swal.fire({ 
        title:'Delete this invoice?', 
        text:'This action cannot be undone.',
        icon:'warning', 
        showCancelButton:true,
        confirmButtonColor:'#ef4444', 
        confirmButtonText:'Yes, delete!'
    }).then(r => {
        if (r.isConfirmed) {
            $.ajax({ 
                url: '{{ route('admin.invoices.destroy', '__ID__') }}'.replace('__ID__', id), 
                type:'DELETE',
                success: r => Swal.fire('Deleted!', r.message, 'success').then(() => location.reload()),
                error:   x => Swal.fire('Error!', x.responseJSON?.message || 'Error', 'error')
            });
        }
    });
}

function previewInvoice(id) {
    Swal.fire({
        title: 'Preview Invoice',
        text: 'Include item descriptions in the preview?',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonColor: '#4f46e5',
        denyButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>With Description',
        denyButtonText: '<i class="bi bi-x-circle me-1"></i>Without Description',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        let url = '{{ route("admin.invoices.print", "__ID__") }}'.replace('__ID__', id);
        if (result.isConfirmed) {
            url += '?desc=1';
            document.getElementById('previewIframe').src = url;
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        } else if (result.isDenied) {
            url += '?desc=0';
            document.getElementById('previewIframe').src = url;
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        }
    });
}
document.getElementById('previewModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('previewIframe').src = '';
});

function downloadPdf(id) {
    Swal.fire({
        title: 'Download PDF',
        text: 'Include item descriptions in the PDF?',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonColor: '#4f46e5',
        denyButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>With Description',
        denyButtonText: '<i class="bi bi-x-circle me-1"></i>Without Description',
        cancelButtonText: 'Cancel',
        customClass: {
            actions: 'flex-wrap gap-2'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route("admin.invoices.pdf", "__ID__") }}'.replace('__ID__', id) + '?desc=1';
        } else if (result.isDenied) {
            window.location.href = '{{ route("admin.invoices.pdf", "__ID__") }}'.replace('__ID__', id) + '?desc=0';
        }
    });
}
</script>
@endpush
