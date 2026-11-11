@extends('layouts.app')
@section('title', $transaction ? 'Edit Payment' : 'Record Payment')

@push('styles')
<style>
.txn-form-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:22px;margin-bottom:16px;}
.tf-label{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:7px;}
.tf-help{font-size:11.5px;color:#94a3b8;margin-top:5px;}
.invoice-hint{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;font-size:12px;color:#475569;}
</style>
@endpush

@section('content')
@php
    $isEdit = filled($transaction);
    $selectedInvoiceId = old('invoice_id', $transaction->invoice_id ?? $selectedInvoice?->id);
    $selectedCustomerId = old('customer_id', $transaction->customer_id ?? $selectedInvoice?->customer_id);
@endphp

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $isEdit ? 'Edit Payment' : 'Record Payment' }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Payments</a></li>
                <li class="breadcrumb-item active">{{ $isEdit ? $transaction->transaction_number : 'Create' }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ $isEdit ? route('admin.transactions.update', $transaction) : route('admin.transactions.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="txn-form-card">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="tf-label" for="invoice_id">Invoice</label>
                        <select name="invoice_id" id="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror">
                            <option value="">Select invoice</option>
                            @foreach($invoices as $invoice)
                            @php $balance = $invoiceBalances[$invoice->id] ?? ['paid' => 0, 'outstanding' => $invoice->total]; @endphp
                            <option value="{{ $invoice->id }}"
                                    data-customer="{{ $invoice->customer_id }}"
                                    data-total="{{ $invoice->total }}"
                                    data-paid="{{ $balance['paid'] }}"
                                    data-outstanding="{{ $balance['outstanding'] }}"
                                    data-label="{{ $invoice->invoice_number }} - {{ $invoice->customer?->name }}"
                                    {{ (string)$selectedInvoiceId === (string)$invoice->id ? 'selected' : '' }}>
                                {{ $invoice->invoice_number }} - {{ $invoice->customer?->name }}
                                ({{ $balance['paid'] > 0 ? 'Partial Paid' : 'Unpaid' }} / Due: {{ format_currency($balance['outstanding']) }})
                            </option>
                            @endforeach
                        </select>
                        @error('invoice_id')<small class="text-danger">{{ $message }}</small>@enderror
                        <div class="tf-help">A payment must belong to a specific invoice.</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="tf-label" for="customer_id">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                            <option value="">No customer</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string)$selectedCustomerId === (string)$customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}{{ $customer->company_name ? ' - ' . $customer->company_name : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('customer_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="tf-label" for="status">Status</label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(['pending','cleared','failed','cancelled'] as $status)
                            <option value="{{ $status }}" {{ old('status', $transaction->status ?? 'cleared') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="tf-label" for="transaction_date">Transaction Date</label>
                        <input type="date" name="transaction_date" id="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror"
                               value="{{ old('transaction_date', optional($transaction?->transaction_date)->format('Y-m-d') ?? now()->toDateString()) }}" required>
                        @error('transaction_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="tf-label" for="amount">Amount</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror"
                               value="{{ old('amount', $transaction->amount ?? '') }}" placeholder="0.00" required>
                        @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="tf-label" for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                            @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'upi' => 'UPI', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" {{ old('payment_method', $transaction->payment_method ?? 'cash') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-12">
                        <label class="tf-label" for="reference_no">Reference Number</label>
                        <input type="text" name="reference_no" id="reference_no" class="form-control @error('reference_no') is-invalid @enderror"
                               value="{{ old('reference_no', $transaction->reference_no ?? '') }}" placeholder="Cheque no, UTR, receipt id, bank reference">
                        @error('reference_no')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12">
                        <label class="tf-label" for="notes">Notes</label>
                        <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="Internal notes, settlement details, remarks">{{ old('notes', $transaction->notes ?? '') }}</textarea>
                        @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="txn-form-card">
                <div class="tf-label">Payment Posting</div>
                <div class="invoice-hint" id="invoiceHint">
                    Select an invoice to auto-map the customer and view the outstanding amount. Cleared payments update invoice status to Partial or Paid.
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>{{ $isEdit ? 'Update Payment' : 'Save Payment' }}
                    </button>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
const amountInput = document.getElementById('amount');
const hint = document.getElementById('invoiceHint');

// Backup all invoice options for filtering
const allInvoiceOptions = Array.from(invoiceSelect.options).map(opt => ({
    value: opt.value,
    text: opt.text,
    dataset: {...opt.dataset},
    selected: opt.selected
}));

const allCustomerOptions = Array.from(customerSelect.options).map(opt => ({
    value: opt.value,
    text: opt.text,
    selected: opt.selected
}));

function restoreCustomerOptions(selectedValue = '') {
    customerSelect.innerHTML = '';
    allCustomerOptions.forEach(opt => {
        const el = document.createElement('option');
        el.value = opt.value;
        el.text = opt.text;
        if (opt.value === selectedValue) el.selected = true;
        customerSelect.appendChild(el);
    });
}

function setCustomerReadonly(readonly, customerId = '') {
    const hiddenFieldId = 'customer_id_hidden';
    const existingHidden = document.getElementById(hiddenFieldId);

    if (readonly && customerId) {
        customerSelect.disabled = true;
        customerSelect.removeAttribute('name');

        const selectedOption = allCustomerOptions.find(opt => opt.value === customerId);
        customerSelect.innerHTML = '';
        if (selectedOption) {
            const el = document.createElement('option');
            el.value = selectedOption.value;
            el.text = selectedOption.text;
            el.selected = true;
            customerSelect.appendChild(el);
        }

        if (!existingHidden) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.id = hiddenFieldId;
            hidden.name = 'customer_id';
            hidden.value = customerId;
            document.querySelector('form').appendChild(hidden);
        } else {
            existingHidden.value = customerId;
        }
    } else {
        customerSelect.disabled = false;
        customerSelect.name = 'customer_id';
        if (existingHidden) {
            existingHidden.remove();
        }
        restoreCustomerOptions(customerId);
    }
}

function updateCustomerSummary(customerId) {
    if (!customerId) {
        hint.textContent = 'Select an invoice to auto-map the customer and view the outstanding amount. Cleared payments update invoice status to Partial or Paid.';
        return;
    }
    let totalOutstanding = 0;
    let matchCount = 0;
    allInvoiceOptions.forEach(opt => {
        if (opt.value && opt.dataset.customer == customerId) {
            totalOutstanding += parseFloat(opt.dataset.outstanding || 0);
            matchCount++;
        }
    });
    
    if (matchCount === 0) {
        hint.innerHTML = `<span class="text-success"><i class="bi bi-check-circle me-1"></i>This customer has no outstanding invoices.</span>`;
    } else {
        hint.innerHTML = `<strong>Customer selected</strong><br>Found ${matchCount} outstanding invoice(s).<br>Total Outstanding Balance: <strong>${totalOutstanding.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong><br><small class="text-muted mt-1 d-block"><i class="bi bi-arrow-down-right me-1"></i>Select an invoice to apply payment.</small>`;
    }
}

function filterInvoicesByCustomer(customerId) {
    const currentValue = invoiceSelect.value;
    invoiceSelect.innerHTML = '<option value="">Select invoice</option>';
    
    allInvoiceOptions.forEach(opt => {
        if (!opt.value) return;
        if (!customerId || opt.dataset.customer == customerId) {
            const el = document.createElement('option');
            el.value = opt.value;
            el.text = opt.text;
            Object.keys(opt.dataset).forEach(k => el.dataset[k] = opt.dataset[k]);
            if (opt.value == currentValue) el.selected = true;
            invoiceSelect.appendChild(el);
        }
    });
    
    // Clear selected invoice if it doesn't belong to the newly selected customer
    if (currentValue && customerId) {
        const matchingOpt = allInvoiceOptions.find(o => o.value == currentValue);
        if (matchingOpt && matchingOpt.dataset.customer != customerId) {
            invoiceSelect.value = '';
            amountInput.value = '';
        }
    }
}

function syncInvoiceFields() {
    const option = invoiceSelect.options[invoiceSelect.selectedIndex];
    if (!option || !option.value) {
        setCustomerReadonly(false, customerSelect.value);
        updateCustomerSummary(customerSelect.value);
        return;
    }

    const invCustomerId = option.dataset.customer;
    if (invCustomerId) {
        setCustomerReadonly(true, invCustomerId);
        filterInvoicesByCustomer(invCustomerId);
        invoiceSelect.value = option.value;
    }
    
    if (!amountInput.value) {
        amountInput.value = Number(option.dataset.outstanding).toFixed(2);
    }
    
    hint.innerHTML = `<strong>${option.dataset.label}</strong><br>
        Invoice total: ${Number(option.dataset.total || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}<br>
        Paid: ${Number(option.dataset.paid || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}<br>
        Outstanding: <strong class="text-danger">${Number(option.dataset.outstanding || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>`;
}

customerSelect.addEventListener('change', function() {
    if (!invoiceSelect.value) {
        filterInvoicesByCustomer(this.value);
        syncInvoiceFields();
    }
});

invoiceSelect.addEventListener('change', function() {
    amountInput.value = ''; // Auto-update amount based on newly selected invoice
    syncInvoiceFields();
});

// Initial setup
if (invoiceSelect.value) {
    syncInvoiceFields();
} else if (customerSelect.value) {
    setCustomerReadonly(false, customerSelect.value);
    filterInvoicesByCustomer(customerSelect.value);
}

</script>
@endpush
