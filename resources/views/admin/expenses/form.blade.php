@extends('layouts.app')
@section('title', $expense ? 'Edit Expense' : 'New Expense')

@push('styles')
<style>
.exp-form-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:22px;margin-bottom:16px}
.ef-label{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:7px}
.ef-help{font-size:11.5px;color:#94a3b8;margin-top:5px}
.exp-hint{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;font-size:12px;color:#475569}
.search-dd{position:relative}
.search-dd-toggle{width:100%;min-height:38px;border:1px solid #ced4da;border-radius:.375rem;background:#fff;color:#212529;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:.375rem .75rem;text-align:left}
.search-dd-toggle:focus{border-color:var(--primary,#4f46e5);box-shadow:0 0 0 3px rgba(79,70,229,.12);outline:0}
.search-dd-label{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.search-dd-menu{display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:30;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 14px 30px rgba(15,23,42,.14);padding:10px}
.search-dd.open .search-dd-menu{display:block}
.search-dd-search{margin-bottom:8px}
.search-dd-options{max-height:230px;overflow:auto}
.search-dd-option{width:100%;border:0;background:transparent;text-align:left;padding:8px 10px;border-radius:8px;font-size:13px;color:#334155;display:block}
.search-dd-option:hover,.search-dd-option.active{background:#eef2ff;color:var(--primary,#4f46e5)}
.search-dd-empty{display:none;padding:12px;text-align:center;color:#94a3b8;font-size:12px}
</style>
@endpush

@section('content')
@php
    $isEdit = filled($expense);
    $selectedInvoiceId = old('invoice_id', $expense->invoice_id ?? $selectedInvoice?->id);
    $selectedCustomerId = old('customer_id', $expense->customer_id ?? $selectedInvoice?->customer_id ?? $selectedTransaction?->customer_id);
    $selectedInvoiceLabel = 'General expense';
    if ($selectedInvoiceId) {
        $selectedInvoiceModel = $invoices->firstWhere('id', (int) $selectedInvoiceId);
        $selectedInvoiceLabel = $selectedInvoiceModel
            ? $selectedInvoiceModel->invoice_number . ' - ' . ($selectedInvoiceModel->customer?->name ?? '')
            : 'Selected invoice';
    }
    $selectedCustomerLabel = 'No customer';
    if ($selectedCustomerId) {
        $selectedCustomerModel = $customers->firstWhere('id', (int) $selectedCustomerId);
        $selectedCustomerLabel = $selectedCustomerModel
            ? $selectedCustomerModel->name . ($selectedCustomerModel->company_name ? ' - ' . $selectedCustomerModel->company_name : '') . ($selectedCustomerModel->mobile ? ' - ' . $selectedCustomerModel->mobile : '')
            : 'Selected customer';
    }
@endphp

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $isEdit ? 'Edit Expense' : 'New Expense' }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
                <li class="breadcrumb-item active">{{ $isEdit ? $expense->expense_number : 'Create' }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ $isEdit ? route('admin.expenses.update', $expense) : route('admin.expenses.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="exp-form-card">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="ef-label" for="invoice_id">Invoice</label>
                        <input type="hidden" name="invoice_id" id="invoice_id" value="{{ $selectedInvoiceId }}">
                        <div class="search-dd" id="invoiceDropdown">
                            <button type="button" class="search-dd-toggle @error('invoice_id') is-invalid @enderror">
                                <span class="search-dd-label" id="invoiceLabel">{{ $selectedInvoiceLabel }}</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="search-dd-menu">
                                <input type="text" id="invoiceSearch" class="form-control search-dd-search" placeholder="Search invoice number or customer...">
                                <div class="search-dd-options">
                                    <button type="button" class="search-dd-option {{ empty($selectedInvoiceId) ? 'active' : '' }}" data-value="" data-label="General expense" data-customer="">General expense</button>
                                @foreach($invoices as $invoice)
                                    <button type="button"
                                            class="search-dd-option {{ (string)$selectedInvoiceId === (string)$invoice->id ? 'active' : '' }}"
                                            data-value="{{ $invoice->id }}"
                                            data-label="{{ $invoice->invoice_number }} - {{ $invoice->customer?->name }}"
                                            data-customer="{{ $invoice->customer_id }}">
                                    {{ $invoice->invoice_number }} - {{ $invoice->customer?->name }}
                                    </button>
                                @endforeach
                                </div>
                                <div class="search-dd-empty">No matching invoices found.</div>
                            </div>
                        </div>
                        @error('invoice_id')<small class="text-danger">{{ $message }}</small>@enderror
                        <div class="ef-help">Open the dropdown and search invoice or customer.</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="ef-label" for="customer_id">Customer</label>
                        <input type="hidden" name="customer_id" id="customer_id" value="{{ $selectedCustomerId }}">
                        <div class="search-dd" id="customerDropdown">
                            <button type="button" class="search-dd-toggle">
                                <span class="search-dd-label" id="customerLabel">{{ $selectedCustomerLabel }}</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="search-dd-menu">
                                <input type="text" id="customerSearch" class="form-control search-dd-search" placeholder="Search customer name, company or mobile...">
                                <div class="search-dd-options">
                                    <button type="button" class="search-dd-option {{ empty($selectedCustomerId) ? 'active' : '' }}" data-value="" data-label="No customer">No customer</button>
                                @foreach($customers as $customer)
                                    <button type="button"
                                            class="search-dd-option {{ (string)$selectedCustomerId === (string)$customer->id ? 'active' : '' }}"
                                            data-value="{{ $customer->id }}"
                                            data-label="{{ $customer->name }}{{ $customer->company_name ? ' - ' . $customer->company_name : '' }}{{ $customer->mobile ? ' - ' . $customer->mobile : '' }}">
                                    {{ $customer->name }}{{ $customer->company_name ? ' - ' . $customer->company_name : '' }}{{ $customer->mobile ? ' - ' . $customer->mobile : '' }}
                                    </button>
                                @endforeach
                                </div>
                                <div class="search-dd-empty">No matching customers found.</div>
                            </div>
                        </div>
                        <div class="ef-help">Open the dropdown and search all customers.</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="ef-label" for="vendor_name">Vendor / Paid To</label>
                        <input type="text" name="vendor_name" id="vendor_name" class="form-control" value="{{ old('vendor_name', $expense->vendor_name ?? '') }}" placeholder="Supplier, labour, subcontractor">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ef-label" for="expense_date">Expense Date</label>
                        <input type="date" name="expense_date" id="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', optional($expense?->expense_date)->format('Y-m-d') ?? now()->toDateString()) }}" required>
                        @error('expense_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ef-label" for="category">Category</label>
                        <input type="text" name="category" id="category" class="form-control" value="{{ old('category', $expense->category ?? 'materials') }}" placeholder="materials, labour, transport">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ef-label" for="amount">Invested Amount</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount ?? '') }}" required>
                        @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ef-label" for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select">
                            @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'upi' => 'UPI', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" {{ old('payment_method', $expense->payment_method ?? 'cash') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ef-label" for="status">Status</label>
                        <select name="status" id="status" class="form-select">
                            @foreach(['pending','approved','paid','cancelled'] as $status)
                            <option value="{{ $status }}" {{ old('status', $expense->status ?? 'paid') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ef-label" for="reference_no">Reference</label>
                        <input type="text" name="reference_no" id="reference_no" class="form-control" value="{{ old('reference_no', $expense->reference_no ?? '') }}" placeholder="Bill no, voucher, UTR">
                    </div>
                    <div class="col-12">
                        <label class="ef-label" for="notes">Notes</label>
                        <textarea name="notes" id="notes" rows="4" class="form-control">{{ old('notes', $expense->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="exp-form-card">
                <div class="ef-label">Expense Posting</div>
                <div class="exp-hint">Choose a customer or invoice for this expense. Paid or approved expenses reduce the net balance shown for the linked invoice.</div>
                <hr>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>{{ $isEdit ? 'Update Expense' : 'Save Expense' }}</button>
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const invoiceSelect = document.getElementById('invoice_id');
const customerSelect = document.getElementById('customer_id');

function closeSearchDropdowns(except = null) {
    document.querySelectorAll('.search-dd.open').forEach(dropdown => {
        if (dropdown !== except) dropdown.classList.remove('open');
    });
}

function setDropdownValue(rootId, hiddenId, labelId, value, label) {
    const root = document.getElementById(rootId);
    document.getElementById(hiddenId).value = value || '';
    document.getElementById(labelId).textContent = label;
    root.querySelectorAll('.search-dd-option').forEach(option => {
        option.classList.toggle('active', String(option.dataset.value || '') === String(value || ''));
    });
}

function initSearchDropdown(rootId, hiddenId, labelId, searchId, onSelect = null) {
    const root = document.getElementById(rootId);
    const toggle = root.querySelector('.search-dd-toggle');
    const search = document.getElementById(searchId);
    const options = Array.from(root.querySelectorAll('.search-dd-option'));
    const empty = root.querySelector('.search-dd-empty');

    toggle.addEventListener('click', () => {
        const isOpen = root.classList.contains('open');
        closeSearchDropdowns(root);
        root.classList.toggle('open', !isOpen);
        if (!isOpen) {
            search.value = '';
            filterOptions('');
            setTimeout(() => search.focus(), 50);
        }
    });

    function filterOptions(term) {
        let visible = 0;
        options.forEach(option => {
            const match = option.textContent.toLowerCase().includes(term);
            option.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        empty.style.display = visible ? 'none' : 'block';
    }

    search.addEventListener('input', () => filterOptions(search.value.toLowerCase().trim()));

    options.forEach(option => {
        option.addEventListener('click', () => {
            setDropdownValue(rootId, hiddenId, labelId, option.dataset.value, option.dataset.label);
            root.classList.remove('open');
            if (onSelect) onSelect(option);
        });
    });
}

initSearchDropdown('customerDropdown', 'customer_id', 'customerLabel', 'customerSearch');
initSearchDropdown('invoiceDropdown', 'invoice_id', 'invoiceLabel', 'invoiceSearch', option => {
    if (option.dataset.customer) {
        const customerOption = document.querySelector(`#customerDropdown .search-dd-option[data-value="${option.dataset.customer}"]`);
        if (customerOption) {
            setDropdownValue('customerDropdown', 'customer_id', 'customerLabel', customerOption.dataset.value, customerOption.dataset.label);
        }
    }
});

document.addEventListener('click', event => {
    if (!event.target.closest('.search-dd')) closeSearchDropdowns();
});
</script>
@endpush
