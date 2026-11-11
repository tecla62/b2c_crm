@extends('layouts.app')
@section('title','Customer Ledger')

@push('styles')
<style>
.ledger-stat{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:18px;height:100%;}
.ledger-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:6px;}
.ledger-value{font-size:22px;font-weight:850;color:#0f172a;}
.ledger-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.ledger-tabs{display:flex;gap:10px;overflow:auto;padding:2px 0 14px;margin-bottom:2px;}
.ledger-tab{background:#fff;border:1px solid #e2e8f0;border-radius:999px;padding:9px 14px;text-decoration:none;color:#475569;font-size:12px;font-weight:850;display:inline-flex;align-items:center;gap:8px;white-space:nowrap;box-shadow:0 1px 4px rgba(15,23,42,.04);}
.ledger-tab.active{background:var(--primary,#4f46e5);border-color:var(--primary,#4f46e5);color:#fff;}
.ledger-customer{font-weight:800;color:#0f172a;}
.ledger-sub{font-size:11.5px;color:#64748b;margin-top:2px;}
.ledger-net{font-weight:850;}
.ledger-net.positive{color:#16a34a;}
.ledger-net.negative{color:#dc2626;}
.ledger-pill{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:3px 9px;font-size:11.5px;font-weight:800;}
.ledger-pill.payment{background:#dcfce7;color:#16a34a;}
.ledger-pill.expense{background:#fee2e2;color:#dc2626;}
.ledger-method{display:inline-flex;align-items:center;gap:5px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:3px 9px;font-size:11.5px;color:#475569;font-weight:600;}
</style>
@endpush

@section('content')
@php
    $sort = request('sort', 'net');
    $includeAll = request()->boolean('include_all');
@endphp
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Customer Ledger</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Payments</a></li>
                <li class="breadcrumb-item active">Customer Ledger</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('transactions.create'))
        <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Record Payment
        </a>
        @endif
        @if(auth()->user()->hasPermission('expenses.create'))
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-outline-primary">
            <i class="bi bi-plus-circle me-2"></i>New Expense
        </a>
        @endif
    </div>
</div>

<div class="ledger-tabs">
    <a href="{{ route('admin.transactions.customer-ledger') }}" class="ledger-tab active">
        <i class="bi bi-person-lines-fill"></i>Customer Wise
    </a>
    <a href="{{ route('admin.transactions.index') }}" class="ledger-tab">
        <i class="bi bi-cash-stack"></i>Payments
    </a>
    <a href="{{ route('admin.expenses.index') }}" class="ledger-tab">
        <i class="bi bi-wallet2"></i>Expenses
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-3">
        <div class="ledger-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="ledger-label">Customers</div>
                    <div class="ledger-value">{{ $summary['customers'] }}</div>
                </div>
                <div class="ledger-icon" style="background:#e0f2fe;color:#0284c7;"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="ledger-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="ledger-label">Payments Received</div>
                    <div class="ledger-value text-success">{{ format_currency($summary['payments']) }}</div>
                </div>
                <div class="ledger-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-arrow-down-left"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="ledger-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="ledger-label">Expenses</div>
                    <div class="ledger-value text-danger">{{ format_currency($summary['expenses']) }}</div>
                </div>
                <div class="ledger-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-arrow-up-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="ledger-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="ledger-label">Net Balance</div>
                    <div class="ledger-value {{ $summary['net'] >= 0 ? 'text-success' : 'text-danger' }}">{{ format_currency($summary['net']) }}</div>
                </div>
                <div class="ledger-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-activity"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100 align-items-center">
            <input type="text" id="searchInput" class="form-control" style="max-width:230px;"
                   placeholder="Search customer" value="{{ request('search') }}">
            <select id="customerInput" class="form-select" style="max-width:260px;">
                <option value="">All Customers</option>
                @foreach($customerOptions as $option)
                <option value="{{ $option->id }}" {{ request('customer_id') == $option->id ? 'selected' : '' }}>
                    {{ $option->name }}{{ $option->company_name ? ' - '.$option->company_name : '' }}
                </option>
                @endforeach
            </select>
            <select id="sortInput" class="form-select" style="max-width:160px;">
                <option value="net" {{ $sort === 'net' ? 'selected' : '' }}>Sort by Net</option>
                <option value="payments" {{ $sort === 'payments' ? 'selected' : '' }}>Sort by Payments</option>
                <option value="expenses" {{ $sort === 'expenses' ? 'selected' : '' }}>Sort by Expenses</option>
            </select>
            <input type="date" id="fromInput" class="form-control" style="max-width:150px;" value="{{ request('from') }}">
            <input type="date" id="toInput" class="form-control" style="max-width:150px;" value="{{ request('to') }}">
            <div class="form-check form-switch ms-1">
                <input class="form-check-input" type="checkbox" id="includeAllInput" {{ $includeAll ? 'checked' : '' }}>
                <label class="form-check-label" for="includeAllInput" style="font-size:12px;font-weight:700;color:#64748b;">All customers</label>
            </div>
            <button class="btn btn-outline-primary" onclick="applyLedgerFilters()">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','customer_id','sort','from','to','include_all']))
            <a href="{{ route('admin.transactions.customer-ledger') }}" class="btn btn-outline-secondary">
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
                    <th>Customer</th>
                    <th>Payments</th>
                    <th>Expenses</th>
                    <th>Net Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $i => $customer)
                @php
                    $payments = (float) ($customer->payment_total ?? 0);
                    $expenses = (float) ($customer->expense_total ?? 0);
                    $net = $payments - $expenses;
                @endphp
                <tr>
                    <td data-label="#">{{ $customers->firstItem() + $i }}</td>
                    <td data-label="Customer">
                        <div class="ledger-customer">{{ $customer->name }}</div>
                        <div class="ledger-sub">{{ $customer->company_name ?: $customer->email ?: $customer->mobile ?: 'Customer account' }}</div>
                    </td>
                    <td data-label="Payments">
                        <div class="fw-800 text-success">{{ format_currency($payments) }}</div>
                        <div class="ledger-sub">{{ $customer->payment_count }} payment{{ $customer->payment_count == 1 ? '' : 's' }}</div>
                    </td>
                    <td data-label="Expenses">
                        <div class="fw-800 text-danger">{{ format_currency($expenses) }}</div>
                        <div class="ledger-sub">{{ $customer->expense_count }} expense{{ $customer->expense_count == 1 ? '' : 's' }}</div>
                    </td>
                    <td data-label="Net Balance">
                        <span class="ledger-net {{ $net >= 0 ? 'positive' : 'negative' }}">{{ format_currency($net) }}</span>
                    </td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            <a href="{{ route('admin.transactions.customer-ledger', array_merge(request()->except('page'), ['customer_id' => $customer->id])) }}"
                               class="act-btn act-view" title="View Ledger"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.customers.show', $customer) }}" class="act-btn" title="Customer" style="background:#e0f2fe;color:#0284c7;"><i class="bi bi-person"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-person-lines-fill fs-2 d-block mb-2"></i>No customer ledger records found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$customers" label="customers" />
</div>

@if($selectedCustomer)
@php
    $selectedLedgerCustomer = $customers->getCollection()->firstWhere('id', $selectedCustomer->id);
    $modalPayments = (float) ($selectedLedgerCustomer?->payment_total ?? 0);
    $modalExpenses = (float) ($selectedLedgerCustomer?->expense_total ?? 0);
    $modalNet = $modalPayments - $modalExpenses;
@endphp
<div class="modal fade" id="customerLedgerModal" tabindex="-1" aria-labelledby="customerLedgerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-800" id="customerLedgerModalLabel">{{ $selectedCustomer->name }} Ledger</h5>
                    <div class="text-muted" style="font-size:12px;">Latest combined payments and expenses</div>
                </div>
                <a href="{{ route('admin.transactions.customer-ledger', request()->except(['customer_id','page'])) }}" class="btn-close" aria-label="Close"></a>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="ledger-stat">
                            <div class="ledger-label">Payments</div>
                            <div class="ledger-value text-success">{{ format_currency($modalPayments) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="ledger-stat">
                            <div class="ledger-label">Expenses</div>
                            <div class="ledger-value text-danger">{{ format_currency($modalExpenses) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="ledger-stat">
                            <div class="ledger-label">Net Balance</div>
                            <div class="ledger-value {{ $modalNet >= 0 ? 'text-success' : 'text-danger' }}">{{ format_currency($modalNet) }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mobile-table-wrap">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Number</th>
                                <th>Invoice</th>
                                <th>Method</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerEntries as $entry)
                            <tr>
                                <td data-label="Date">{{ $entry['date']->format('d M Y') }}</td>
                                <td data-label="Type">
                                    <span class="ledger-pill {{ strtolower($entry['type']) }}">
                                        <i class="bi {{ $entry['type'] === 'Payment' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>{{ $entry['type'] }}
                                    </span>
                                </td>
                                <td data-label="Number"><a href="{{ $entry['url'] }}" class="text-decoration-none fw-700">{{ $entry['number'] }}</a></td>
                                <td data-label="Invoice">
                                    @if($entry['invoice'])
                                    <a href="{{ route('admin.invoices.show', $entry['invoice']) }}" class="text-decoration-none">{{ $entry['invoice']->invoice_number }}</a>
                                    @else
                                    <span class="text-muted">General</span>
                                    @endif
                                </td>
                                <td data-label="Method"><span class="ledger-method"><i class="bi bi-wallet2"></i>{{ str_replace('_',' ', ucfirst($entry['method'])) }}</span></td>
                                <td data-label="Amount">
                                    <span class="fw-800 {{ $entry['amount'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $entry['amount'] >= 0 ? '+' : '-' }}{{ format_currency(abs($entry['amount'])) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No ledger entries for this customer.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.customers.show', $selectedCustomer) }}" class="btn btn-outline-primary">
                    <i class="bi bi-person me-1"></i>Open Customer
                </a>
                <a href="{{ route('admin.transactions.customer-ledger', request()->except(['customer_id','page'])) }}" class="btn btn-secondary">
                    Close
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function applyLedgerFilters() {
    const params = new URLSearchParams({
        search: $('#searchInput').val(),
        customer_id: $('#customerInput').val(),
        sort: $('#sortInput').val(),
        from: $('#fromInput').val(),
        to: $('#toInput').val(),
        include_all: $('#includeAllInput').is(':checked') ? '1' : ''
    });
    [...params.entries()].forEach(([key, value]) => {
        if (!value || (key === 'sort' && value === 'net')) params.delete(key);
    });
    window.location.href = `{{ route('admin.transactions.customer-ledger') }}?${params.toString()}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyLedgerFilters(); });

@if($selectedCustomer)
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('customerLedgerModal');
    if (!modalEl) return;
    new bootstrap.Modal(modalEl).show();
});
@endif
</script>
@endpush
