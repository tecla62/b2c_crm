@extends('layouts.app')
@section('title','Payments')

@push('styles')
<style>
.txn-stat{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:18px;height:100%;}
.txn-stat-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:6px;}
.txn-stat-value{font-size:22px;font-weight:850;color:#0f172a;}
.txn-stat-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.txn-ref{font-size:11.5px;color:#64748b;margin-top:2px;}
.txn-method{display:inline-flex;align-items:center;gap:5px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:3px 9px;font-size:11.5px;color:#475569;font-weight:600;}
.invoice-pay-state{display:inline-flex;align-items:center;gap:4px;border-radius:999px;padding:2px 8px;font-size:10.5px;font-weight:800;margin-top:5px;}
.invoice-pay-state.partial{background:#fef3c7;color:#d97706;}
.invoice-pay-state.full{background:#dcfce7;color:#16a34a;}
.invoice-pay-state.unpaid{background:#f1f5f9;color:#64748b;}
.payment-tabs{display:flex;gap:10px;overflow:auto;padding:2px 0 14px;margin-bottom:2px;}
.payment-tab{background:#fff;border:1px solid #e2e8f0;border-radius:999px;padding:9px 14px;text-decoration:none;color:#475569;font-size:12px;font-weight:850;display:inline-flex;align-items:center;gap:8px;white-space:nowrap;box-shadow:0 1px 4px rgba(15,23,42,.04);}
.payment-tab.active{background:var(--primary,#4f46e5);border-color:var(--primary,#4f46e5);color:#fff;}
.payment-tab-count{min-width:22px;height:22px;padding:0 7px;border-radius:999px;background:#f1f5f9;color:#64748b;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;}
.payment-tab.active .payment-tab-count{background:rgba(255,255,255,.2);color:#fff;}
</style>
@endpush

@section('content')
@php
    $activeState = request('payment_state', 'all');
    $tabBase = request()->except(['payment_state', 'page']);
@endphp
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Payments</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Payments</li>
            </ol>
        </nav>
    </div>
    @if(auth()->user()->hasPermission('transactions.create'))
    <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Record Payment
    </a>
    @endif
</div>

<div class="payment-tabs">
    <a href="{{ route('admin.transactions.customer-ledger') }}"
       class="payment-tab">
        <i class="bi bi-person-lines-fill"></i>Customer Ledger
    </a>
    <a href="{{ route('admin.transactions.index', $tabBase) }}"
       class="payment-tab {{ $activeState === 'all' ? 'active' : '' }}">
        <i class="bi bi-grid"></i>All Payments
        <span class="payment-tab-count">{{ $stateCounts['all'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.transactions.index', array_merge($tabBase, ['payment_state' => 'partial'])) }}"
       class="payment-tab {{ $activeState === 'partial' ? 'active' : '' }}">
        <i class="bi bi-hourglass-split"></i>Partially Paid
        <span class="payment-tab-count">{{ $stateCounts['partial'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.transactions.index', array_merge($tabBase, ['payment_state' => 'paid'])) }}"
       class="payment-tab {{ $activeState === 'paid' ? 'active' : '' }}">
        <i class="bi bi-check-circle"></i>Fully Paid
        <span class="payment-tab-count">{{ $stateCounts['paid'] ?? 0 }}</span>
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-3">
        <div class="txn-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="txn-stat-label">Collected</div>
                    <div class="txn-stat-value text-success">{{ format_currency($summary['paid']) }}</div>
                </div>
                <div class="txn-stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-arrow-down-left"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="txn-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="txn-stat-label">Invoice Value</div>
                    <div class="txn-stat-value">{{ format_currency($summary['invoice_total']) }}</div>
                </div>
                <div class="txn-stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-arrow-up-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="txn-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="txn-stat-label">Outstanding</div>
                    <div class="txn-stat-value text-danger">{{ format_currency($summary['outstanding']) }}</div>
                </div>
                <div class="txn-stat-icon" style="background:#e0e7ff;color:var(--primary,#4f46e5);"><i class="bi bi-activity"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="txn-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="txn-stat-label">Pending Payments</div>
                    <div class="txn-stat-value text-warning">{{ format_currency($summary['pending']) }}</div>
                </div>
                <div class="txn-stat-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-hourglass-split"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:240px;"
                   placeholder="Search receipt / customer / invoice" value="{{ request('search') }}">
            <select id="customerFilter" class="form-select" style="max-width:220px;">
                <option value="">All Customers</option>
                @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}{{ $customer->company_name ? ' — ' . $customer->company_name : '' }}</option>
                @endforeach
            </select>
            <select id="statusFilter" class="form-select" style="max-width:140px;">
                <option value="">All Status</option>
                @foreach(['pending','cleared','failed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <input type="date" id="fromInput" class="form-control" style="max-width:150px;" value="{{ request('from') }}">
            <input type="date" id="toInput" class="form-control" style="max-width:150px;" value="{{ request('to') }}">
            <button class="btn btn-outline-primary" onclick="applyFilters()">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','type','status','from','to','payment_state']))
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Clear
            </a>
            @endif
        </div>
    </div>

    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th><th>Receipt</th><th>Customer / Invoice</th><th>Date</th>
                    <th>Method</th><th>Amount</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $i => $txn)
                <tr>
                    <td data-label="#">{{ $transactions->firstItem() + $i }}</td>
                    <td data-label="Receipt">
                        <div class="fw-700">{{ $txn->transaction_number }}</div>
                        <div class="mt-1"><span class="badge" style="background:#dbeafe;color:#1d4ed8;">Payment</span></div>
                        @if($txn->reference_no)<div class="txn-ref">Ref: {{ $txn->reference_no }}</div>@endif
                    </td>
                    <td data-label="Customer / Invoice">
                        <div class="fw-600">{{ $txn->customer?->name ?? 'General Ledger' }}</div>
                        @if($txn->invoice)
                        @php
                            $balance = $invoiceBalances[$txn->invoice->id] ?? ['paid' => 0, 'outstanding' => $txn->invoice->total];
                            $paid = (float) ($balance['paid'] ?? 0);
                            $outstanding = (float) ($balance['outstanding'] ?? $txn->invoice->total);
                            $payState = $outstanding <= 0.01 ? 'full' : ($paid > 0 ? 'partial' : 'unpaid');
                            $payStateLabel = $payState === 'full' ? 'Full Paid' : ($payState === 'partial' ? 'Partial Paid' : 'Unpaid');
                        @endphp
                        <a href="{{ route('admin.invoices.show', $txn->invoice) }}" class="text-decoration-none" style="font-size:11.5px;">
                            <i class="bi bi-receipt me-1"></i>{{ $txn->invoice->invoice_number }}
                        </a>
                        <div>
                            <span class="invoice-pay-state {{ $payState }}">
                                <i class="bi {{ $payState === 'full' ? 'bi-check-circle' : ($payState === 'partial' ? 'bi-hourglass-split' : 'bi-clock') }}"></i>
                                {{ $payStateLabel }}
                            </span>
                        </div>
                        @endif
                    </td>
                    <td data-label="Date">{{ $txn->transaction_date->format('d M Y') }}</td>
                    <td data-label="Method"><span class="txn-method"><i class="bi bi-wallet2"></i>{{ str_replace('_',' ', ucfirst($txn->payment_method)) }}</span></td>
                    <td data-label="Amount">
                        <span class="fw-800 text-success">
                            +{{ format_currency($txn->amount) }}
                        </span>
                    </td>
                    <td data-label="Status" id="status-badge-{{ $txn->id }}">{!! $txn->status_badge !!}</td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            <a href="{{ route('admin.transactions.show', $txn) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.transactions.receipt', $txn) }}" target="_blank" class="act-btn" title="Receipt" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-receipt"></i></a>
                            @if(auth()->user()->hasPermission('transactions.edit'))
                            <button type="button"
                                    class="act-btn"
                                    title="Send Receipt Mail"
                                    onclick="sendReceiptMail({{ $txn->id }}, @js($txn->customer?->email ?? ''))"
                                    style="background:#e0f2fe;color:#0284c7;">
                                <i class="bi bi-envelope"></i>
                            </button>
                            <a href="{{ route('admin.transactions.edit', $txn) }}" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                            <div class="dropdown d-inline-block">
                                <button class="act-btn" title="Change Status" data-bs-toggle="dropdown" style="background:#f0f4ff;color:var(--primary,#4f46e5);">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="min-width:140px;font-size:13px;">
                                    @foreach(['pending','cleared','failed','cancelled'] as $s)
                                    <li><a class="dropdown-item {{ $txn->status === $s ? 'fw-700 text-primary' : '' }}" href="#" onclick="changeStatus({{ $txn->id }}, '{{ $s }}');return false;">{{ $txn->status === $s ? '✓ ' : '' }}{{ ucfirst($s) }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @if(auth()->user()->hasPermission('transactions.delete'))
                            <button onclick="deleteTransaction({{ $txn->id }})" class="act-btn act-delete" title="Delete"><i class="bi bi-trash"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-cash-stack fs-2 d-block mb-2"></i>No payments found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$transactions" label="payments" />
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const params = new URLSearchParams({
        payment_state: @json(request('payment_state')),
        search: $('#searchInput').val(),
        customer_id: $('#customerFilter').val(),
        status: $('#statusFilter').val(),
        from: $('#fromInput').val(),
        to: $('#toInput').val()
    });
    [...params.entries()].forEach(([key, value]) => {
        if (!value || value === 'all') params.delete(key);
    });
    window.location.href = `{{ route('admin.transactions.index') }}?${params.toString()}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyFilters(); });

const transactionStatusUrl = id => '{{ route("admin.transactions.status", ["transaction" => "__TXN__"]) }}'.replace('__TXN__', id);
const transactionSendReceiptUrl = id => '{{ route("admin.transactions.send-receipt", ["transaction" => "__TXN__"]) }}'.replace('__TXN__', id);
const transactionDeleteUrl = id => '{{ route("admin.transactions.destroy", ["transaction" => "__TXN__"]) }}'.replace('__TXN__', id);

function changeStatus(id, status) {
    $.post(transactionStatusUrl(id), { status },
        r => {
            document.getElementById(`status-badge-${id}`).innerHTML = r.badge;
            Swal.fire({ icon:'success', text:r.message, timer:1500, showConfirmButton:false });
        }
    ).fail(x => Swal.fire('Error', x.responseJSON?.message || 'Error', 'error'));
}

function sendReceiptMail(id, customerEmail) {
    Swal.fire({
        title: 'Send payment receipt?',
        input: 'email',
        inputValue: customerEmail || '',
        inputPlaceholder: 'customer@example.com',
        showCancelButton: true,
        confirmButtonText: 'Send Mail',
        preConfirm: email => {
            if (!email) {
                Swal.showValidationMessage('Please enter an email address.');
                return false;
            }
            return email;
        }
    }).then(result => {
        if (!result.isConfirmed) return;

        $.post(transactionSendReceiptUrl(id), { to: result.value })
            .done(r => Swal.fire({ icon:'success', text:r.message, timer:2200, showConfirmButton:false }))
            .fail(x => Swal.fire('Error', x.responseJSON?.message || 'Failed to send receipt.', 'error'));
    });
}

function deleteTransaction(id) {
    Swal.fire({ title:'Delete this transaction?', text:'Linked invoice balances will be recalculated.', icon:'warning', showCancelButton:true, confirmButtonColor:'#ef4444', confirmButtonText:'Yes, delete!' })
    .then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: transactionDeleteUrl(id), type:'DELETE',
            success: r => Swal.fire('Deleted!', r.message,'success').then(()=>location.reload()),
            error: x => Swal.fire('Error!', x.responseJSON?.message || 'Error','error')
        });
    });
}
</script>
@endpush
