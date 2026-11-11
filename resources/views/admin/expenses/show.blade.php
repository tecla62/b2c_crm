@extends('layouts.app')
@section('title', $expense->expense_number)

@push('styles')
<style>
.exp-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:20px;margin-bottom:16px}
.meta-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:4px}
.meta-value{font-size:14px;font-weight:650;color:#1e293b}
.big-amount{font-size:28px;font-weight:900;letter-spacing:-.4px}
.detail-row{display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #f1f5f9}
.detail-row:last-child{border-bottom:0}.detail-row span:first-child{font-size:12px;color:#64748b}.detail-row span:last-child{font-size:13px;font-weight:700;text-align:right}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">{{ $expense->expense_number }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
                <li class="breadcrumb-item active">{{ $expense->expense_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if(auth()->user()->hasPermission('expenses.edit'))
        <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="exp-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="meta-label">Invested Amount</div>
                    <div class="big-amount text-danger">-{{ format_currency($expense->amount) }}</div>
                </div>
                <div class="text-end">{!! $expense->status_badge !!}</div>
            </div>
        </div>
        <div class="exp-card">
            <div class="meta-label mb-3">Expense Details</div>
            <div class="detail-row"><span>Date</span><span>{{ $expense->expense_date->format('d M Y') }}</span></div>
            <div class="detail-row"><span>Category</span><span>{{ ucfirst($expense->category) }}</span></div>
            <div class="detail-row"><span>Vendor</span><span>{{ $expense->vendor_name ?: '—' }}</span></div>
            <div class="detail-row"><span>Payment Method</span><span>{{ str_replace('_',' ', ucfirst($expense->payment_method)) }}</span></div>
            <div class="detail-row"><span>Reference</span><span>{{ $expense->reference_no ?: '—' }}</span></div>
            <div class="detail-row"><span>Created By</span><span>{{ $expense->createdBy?->name ?? '—' }}</span></div>
        </div>
        @if($expense->notes)
        <div class="exp-card">
            <div class="meta-label mb-2">Notes</div>
            <div style="font-size:13px;color:#475569;white-space:pre-line;line-height:1.7;">{{ $expense->notes }}</div>
        </div>
        @endif
    </div>
    <div class="col-12 col-lg-4">
        <div class="exp-card">
            <div class="meta-label mb-3">Links</div>
            <div class="detail-row"><span>Invoice</span><span>@if($expense->invoice)<a href="{{ route('admin.invoices.show', $expense->invoice) }}">{{ $expense->invoice->invoice_number }}</a>@else — @endif</span></div>
            <div class="detail-row"><span>Customer</span><span>{{ $expense->customer?->name ?? '—' }}</span></div>
        </div>
    </div>
</div>
@endsection
