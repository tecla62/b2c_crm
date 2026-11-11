@extends('layouts.app')
@section('title','Quotations')

@push('styles')
<style>
.act-btn.act-download { background:#f0fdf4; color:#16a34a; }
.act-btn.act-download:hover { background:#16a34a; color:#fff; }
.act-btn.act-preview { background:#fef3c7; color:#d97706; }
.act-btn.act-preview:hover { background:#d97706; color:#fff; }
.q-total-val { color: #0f172a; }
body.dark-mode .q-total-val { color: #f8fafc !important; }
body.dark-mode .table tbody tr td { color: #e2e8f0; }
body.dark-mode .table-responsive { background: #1e293b; }
@media(max-width:767px) {
    .action-btns { flex-wrap:wrap; }
    #previewModal .modal-dialog { margin:8px; max-width:calc(100% - 16px); }
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Quotations</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Quotations</li>
            </ol>
        </nav>
    </div>
    @if(auth()->user()->hasPermission('quotations.create'))
    <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Quotation
    </a>
    @endif
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:260px;"
                   placeholder="Search number / customer…" value="{{ request('search') }}">
            <select id="statusFilter" class="form-select" style="max-width:150px;">
                <option value="">All Status</option>
                @foreach(['draft','sent','accepted','rejected','expired'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary" onclick="applyFilters()">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">
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
                    <th>Quotation / Invoice</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Valid Until</th>
                    <th>Total & Invoice</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations as $i => $q)
                <tr>
                    <td data-label="#">{{ $quotations->firstItem() + $i }}</td>
                    <td data-label="Quotation / Invoice">
                        <a href="{{ route('admin.quotations.show', $q) }}" class="fw-700 text-primary text-decoration-none d-block">
                            {{ $q->quotation_number }}
                        </a>
                        @if($q->invoice)
                        <div class="mt-1">
                            <a href="{{ route('admin.invoices.show', $q->invoice) }}"
                               class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none"
                               title="Converted to Invoice #{{ $q->invoice->invoice_number }}"
                               style="font-size:11px;font-weight:500;">
                                <i class="bi bi-receipt me-1"></i>{{ $q->invoice->invoice_number }}
                            </a>
                        </div>
                        @endif
                    </td>
                    <td data-label="Customer">
                        <div class="fw-600">{{ $q->customer?->name ?? 'N/A' }}</div>
                        <div class="text-muted" style="font-size:11.5px;">{{ $q->customer?->company_name ?? '' }}</div>
                    </td>
                    <td data-label="Date">{{ $q->date->format('d M Y') }}</td>
                    <td data-label="Valid Until">{{ $q->valid_until?->format('d M Y') ?? '—' }}</td>
                    <td data-label="Total & Invoice">
                        <div class="fw-700 fs-6 q-total-val">{{ format_currency($q->total) }}</div>
                        @if($q->invoice)
                            @php
                                $diff = (float)$q->total - (float)$q->invoice->total;
                            @endphp
                            <div class="d-flex flex-column gap-1 mt-1" style="font-size:11.5px;line-height:1.2;">
                                <div class="text-muted">
                                    Inv: <strong class="q-total-val">{{ format_currency($q->invoice->total) }}</strong>
                                </div>
                                @if((float)($q->invoice->discount ?? 0) > 0)
                                <div class="text-danger fw-600">
                                    <i class="bi bi-tag-fill me-1" style="font-size:10px;"></i>Disc: −{{ format_currency($q->invoice->discount) }}
                                </div>
                                @elseif($diff > 0.01)
                                <div class="text-success fw-600">
                                    <i class="bi bi-arrow-down-circle me-1" style="font-size:10px;"></i>Saved: −{{ format_currency($diff) }}
                                </div>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td data-label="Status" id="status-badge-{{ $q->id }}">{!! $q->status_badge !!}</td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            <a href="{{ route('admin.quotations.show', $q) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            @if($q->invoice)
                            <a href="{{ route('admin.invoices.show', $q->invoice) }}" class="act-btn" style="background:#dcfce7;color:#15803d;" title="View Linked Invoice"><i class="bi bi-receipt-cutoff"></i></a>
                            @elseif(auth()->user()->hasPermission('invoices.create'))
                            <a href="{{ route('admin.invoices.create', ['quotation_id' => $q->id]) }}" class="act-btn" style="background:#e0e7ff;color:#4338ca;" title="Convert to Invoice"><i class="bi bi-receipt"></i></a>
                            @endif
                            <button type="button" onclick="downloadPdf('{{ $q->id }}')" class="act-btn act-download" title="Download PDF"><i class="bi bi-download"></i></button>
                            @if(auth()->user()->hasPermission('quotations.edit'))
                            <a href="{{ route('admin.quotations.edit', $q) }}" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                            <div class="dropdown d-inline-block">
                                <button class="act-btn" title="Change Status" data-bs-toggle="dropdown" style="background:#f0f4ff;color:var(--primary,#4f46e5);">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="min-width:140px;font-size:13px;">
                                    @foreach(['draft','sent','accepted','approved','rejected','expired'] as $s)
                                    <li>
                                        <a class="dropdown-item {{ $q->status === $s ? 'fw-700 text-primary' : '' }}"
                                           href="#"
                                           onclick="changeQuotationStatus({{ $q->id }},'{{ $s }}');return false;">
                                            {{ $q->status === $s ? '✓ ' : '' }}{{ ucfirst($s) }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @if(auth()->user()->hasPermission('quotations.delete'))
                            <button onclick="deleteQuotation({{ $q->id }})" class="act-btn act-delete" title="Delete"><i class="bi bi-trash"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-file-earmark-text fs-2 d-block mb-2"></i>No quotations found
                        @if(auth()->user()->hasPermission('quotations.create'))
                        <br><a href="{{ route('admin.quotations.create') }}" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-plus-circle me-1"></i>Create First Quotation
                        </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$quotations" label="quotations" />
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    window.location.href = `{{ route('admin.quotations.index') }}?search=${$('#searchInput').val()}&status=${$('#statusFilter').val()}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyFilters(); });

function changeQuotationStatus(id, status) {
    $.post('{{ route("admin.quotations.status", "__ID__") }}'.replace('__ID__', id), { status },
        r => {
            document.getElementById(`status-badge-${id}`).innerHTML = r.badge;
            Swal.fire({ icon:'success', text: r.message, timer:1500, showConfirmButton:false });
            if (r.invoice_redirect) {
                setTimeout(() => window.location = r.invoice_redirect, 1600);
            }
        }
    ).fail(x => Swal.fire('Error', x.responseJSON?.message || 'Error', 'error'));
}

function deleteQuotation(id) {
    Swal.fire({ title:'Delete this quotation?', icon:'warning', showCancelButton:true,
        confirmButtonColor:'#ef4444', confirmButtonText:'Yes, delete!'
    }).then(r => {
        if (r.isConfirmed) $.ajax({ url:'{{ route("admin.quotations.destroy", "__ID__") }}'.replace('__ID__', id), type:'DELETE',
            success: r => Swal.fire('Deleted!', r.message,'success').then(()=>location.reload()),
            error:   x => Swal.fire('Error!', x.responseJSON?.message||'Error','error')
        });
    });
}

// Preview feature removed

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
            window.location.href = '{{ route("admin.quotations.pdf", "__ID__") }}'.replace('__ID__', id) + '?desc=1';
        } else if (result.isDenied) {
            window.location.href = '{{ route("admin.quotations.pdf", "__ID__") }}'.replace('__ID__', id) + '?desc=0';
        }
    });
}
</script>
@endpush
