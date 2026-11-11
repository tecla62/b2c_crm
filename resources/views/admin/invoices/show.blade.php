@extends('layouts.app')
@section('title', $invoice->invoice_number)

@push('styles')
<style>
.show-meta-card { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:20px; margin-bottom:16px; }
.meta-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#94a3b8; margin-bottom:3px; }
.meta-value { font-size:14px; font-weight:600; color:#1e293b; }

/* SL group table */
.sl-table { width:100%; border-collapse:collapse; }
.sl-table thead tr { background:#f8fafc; }
.sl-table thead th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#64748b; padding:10px 16px; border-bottom:2px solid #f1f5f9; }
.sl-table thead th.r { text-align:right; }
.sl-group-row td { padding:0; border-bottom:2px solid #e2e8f0; }
.sl-group-inner { padding:14px 16px; }
.sl-group-header-row { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.sl-badge { width:26px; height:26px; border-radius:50%; background:var(--primary,#4f46e5); color:#fff; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sl-mode-pill { display:inline-flex; align-items:center; gap:4px; background:#f0f4ff; color:var(--primary,#4f46e5); border-radius:6px; padding:2px 8px; font-size:10.5px; font-weight:600; }
.sl-area-info { font-size:11.5px; color:#818cf8; margin-top:3px; }
.sl-price-cell { text-align:right; padding:14px 16px; vertical-align:top; white-space:nowrap; }
.svc-row { display:flex; align-items:flex-start; gap:8px; padding:6px 0; border-top:1px solid #f8fafc; }
.svc-row:first-child { border-top:none; }
.svc-dot { width:6px; height:6px; border-radius:50%; background:#cbd5e1; flex-shrink:0; margin-top:5px; }
.svc-title { font-size:13px; font-weight:600; color:#1e293b; }
.svc-desc { font-size:11.5px; color:#64748b; margin-top:2px; line-height:1.5; }
.cat-badge { display:inline-block; background:#eef2ff; color:var(--primary,#4f46e5); border-radius:5px; padding:1px 7px; font-size:10.5px; font-weight:600; margin-top:3px; }

@media(max-width:767px) {
    .sl-table thead { display:none; }
    .sl-group-row td, .sl-price-cell { display:block; padding:0; }
    .sl-price-cell { text-align:left; padding:0 16px 14px; }
    .sl-group-inner { padding-bottom:4px; }
}

/* Terms accordion (matches form page style) */
.terms-acc-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(15, 23, 42, .04);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.terms-acc-head {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 10px 12px;
    border: none;
    background: #f8fafc;
    text-align: left;
    cursor: pointer;
    transition: background .15s;
}
.terms-acc-head:hover { background: #f1f5f9; }
.terms-acc-head[aria-expanded="true"] { background: #fff; border-bottom: 1px solid #f1f5f9; }
.terms-acc-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #fff7ed;
    color: #ea580c;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.terms-acc-title {
    flex: 1;
    min-width: 0;
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.3;
}
.btn-edit-terms-show {
    font-size: 11px;
    color: var(--primary, #4f46e5);
    background: #fff;
    border: 1px solid #e0e7ff;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
    text-decoration: none;
    transition: background .15s;
}
.btn-edit-terms-show:hover {
    background: #e0e7ff;
    color: var(--primary, #4f46e5);
}
.terms-acc-chevron {
    font-size: 14px;
    color: #94a3b8;
    flex-shrink: 0;
    transition: transform .2s ease;
}
.terms-acc-head[aria-expanded="true"] .terms-acc-chevron {
    transform: rotate(180deg);
    color: var(--primary, #4f46e5);
}
.terms-acc-body {
    padding: 12px 14px 14px;
    font-size: 13px;
    color: #475569;
    line-height: 1.65;
    white-space: pre-line;
    flex: 1;
}
.terms-acc-empty {
    color: #94a3b8;
    font-style: italic;
    font-size: 12px;
}
</style>
@endpush

@section('content')

@php
    // Use invoice's own items; fall back to quotation items for legacy converted invoices
    $sourceItems = $invoice->items->count() ? $invoice->items : ($invoice->quotation?->items ?? collect());
    $groups = $sourceItems->groupBy('sl_no')->sortKeys();
@endphp

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">{{ $invoice->invoice_number }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>Print
        </a>
        <button type="button" onclick="downloadPdf('{{ $invoice->id }}')" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i>Download PDF
        </button>
        @if(auth()->user()->hasPermission('invoices.edit'))
        <button type="button" class="btn btn-info text-white" onclick="sendInvoiceEmail()" title="Send invoice PDF to customer email">
            <i class="bi bi-envelope me-1"></i>Send Email
        </button>
        @if(auth()->user()->hasPermission('transactions.create'))
        <a href="{{ route('admin.transactions.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success">
            <i class="bi bi-cash-stack me-1"></i>Record Payment
        </a>
        @endif
        <button class="btn btn-outline-primary" onclick="duplicateInvoice({{ $invoice->id }})">
            <i class="bi bi-copy me-1"></i>Duplicate
        </button>
        @if($invoice->quotation)
        <a href="{{ route('admin.quotations.show', $invoice->quotation) }}" class="btn btn-outline-info">
            <i class="bi bi-link-45deg me-1"></i>View Quotation
        </a>
        @endif
        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endif
    </div>
</div>

<div class="row g-3">

    {{-- ── Left ── --}}
    <div class="col-12 col-lg-8">

        {{-- Status bar --}}
        <div class="show-meta-card d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div>
                    <div class="meta-label">Status</div>
                    <div id="statusBadgeWrap">{!! $invoice->status_badge !!}</div>
                </div>
                <div>
                    <div class="meta-label">Date</div>
                    <div class="meta-value">{{ $invoice->date->format('d M Y') }}</div>
                </div>
                @if($invoice->due_date)
                <div>
                    <div class="meta-label">Due Date</div>
                    <div class="meta-value {{ $invoice->due_date->isPast() && !in_array($invoice->status,['paid','cancelled']) ? 'text-danger' : '' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                    </div>
                </div>
                @endif
                @if($invoice->subject)
                <div>
                    <div class="meta-label">Subject</div>
                    <div class="meta-value">{{ $invoice->subject }}</div>
                </div>
                @endif
            </div>
            @if(auth()->user()->hasPermission('invoices.edit'))
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    Change Status
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach(['draft','sent','paid','partial','overdue','cancelled'] as $s)
                    <li><a class="dropdown-item" href="#" onclick="changeStatus('{{ $s }}');return false;">
                        {{ ucfirst($s) }}
                    </a></li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        @if($invoice->quotation)
        {{-- Linked Quotation Notice Banner --}}
        <div class="show-meta-card mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:10px;background:#e0e7ff;color:var(--primary,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div>
                        <div class="fw-700 text-dark" style="font-size:15px;">
                            Generated from Quotation <a href="{{ route('admin.quotations.show', $invoice->quotation) }}" class="text-primary text-decoration-none">#{{ $invoice->quotation->quotation_number }}</a>
                        </div>
                        <div class="text-muted" style="font-size:12px;">
                            Original Quotation Amount: <strong class="text-secondary">{{ format_currency($invoice->quotation->total) }}</strong>
                            &bull; Status: {!! $invoice->quotation->status_badge !!}
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 ms-auto">
                    @php
                        $quotTot = (float)$invoice->quotation->total;
                        $invTot  = (float)$invoice->total;
                        $diff    = $quotTot - $invTot;
                        $directDisc = (float)($invoice->discount ?? 0);
                    @endphp
                    @if($directDisc > 0)
                        <div class="text-end">
                            <div class="text-muted" style="font-size:11px;font-weight:600;">DISCOUNT APPLIED</div>
                            <span class="badge bg-danger fs-6">−{{ format_currency($directDisc) }}</span>
                        </div>
                    @elseif($diff > 0.01)
                        <div class="text-end">
                            <div class="text-muted" style="font-size:11px;font-weight:600;">PRICE REDUCED</div>
                            <span class="badge bg-success fs-6">−{{ format_currency($diff) }}</span>
                        </div>
                    @endif
                    <a href="{{ route('admin.quotations.show', $invoice->quotation) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>View Quotation
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- SL Groups --}}
        <div class="show-meta-card p-0 overflow-hidden">
            <div style="padding:14px 20px;border-bottom:1px solid #f1f5f9;font-weight:700;font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:.6px;">
                <i class="bi bi-list-ol me-2"></i>Line Items
                <span class="ms-2 badge" style="background:#e0e7ff;color:var(--primary,#4f46e5);font-size:11px;">
                    {{ $groups->count() }} SL {{ Str::plural('Group', $groups->count()) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="sl-table">
                    <thead>
                        <tr>
                            <th style="width:36px;">SL</th>
                            <th>Services</th>
                            <th style="width:130px;">Area / Mode</th>
                            <th class="r" style="width:120px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $slNo => $items)
                        @php
                            $first    = $items->first();
                            $mode     = $first->area_mode ?? 'manual';
                            $groupAmt = $items->sum('total');
                        @endphp
                        <tr class="sl-group-row">
                            <td style="vertical-align:top;padding:14px 16px;">
                                <div class="sl-badge">{{ $slNo }}</div>
                            </td>
                            <td>
                                <div class="sl-group-inner">
                                    {{-- Mode pill --}}
                                    <div class="mb-2">
                                        @if($mode === 'sqft')
                                            <span class="sl-mode-pill"><i class="bi bi-bounding-box"></i> SqFt × Rate</span>
                                        @elseif($mode === 'nos')
                                            <span class="sl-mode-pill"><i class="bi bi-123"></i> Nos × Rate</span>
                                        @else
                                            <span class="sl-mode-pill"><i class="bi bi-input-cursor-text"></i> Fixed</span>
                                        @endif
                                    </div>

                                    {{-- Area info --}}
                                    @if($mode === 'sqft' && $first->area_value)
                                        <div class="sl-area-info mb-2">
                                            📐 {{ number_format($first->area_value, 2) }} sqft
                                            @if($first->rate_per_sft)
                                                × {{ format_currency($first->rate_per_sft) }}/sqft
                                            @endif
                                        </div>
                                    @elseif($mode === 'nos' && $first->area_nos)
                                        <div class="sl-area-info mb-2">
                                            🔢 {{ number_format($first->area_nos, 0) }} nos
                                            @if($first->rate_per_nos)
                                                × {{ format_currency($first->rate_per_nos) }}/unit
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Services list --}}
                                    @foreach($items as $item)
                                    <div class="svc-row">
                                        <div class="svc-dot mt-1"></div>
                                        <div style="flex:1;min-width:0;">
                                            <div class="svc-title">{{ $item->title }}</div>
                                            @if($item->description)
                                                <div class="svc-desc">{{ $item->description }}</div>
                                            @endif
                                            @if($item->category)
                                                <span class="cat-badge">{{ $item->category->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td style="vertical-align:top;padding:14px 16px;color:#64748b;font-size:12px;">
                                @if($mode === 'sqft' && $first->area_value)
                                    {{ number_format($first->area_value, 2) }} sqft
                                @elseif($mode === 'nos' && $first->area_nos)
                                    {{ number_format($first->area_nos, 0) }} nos
                                @else
                                    —
                                @endif
                            </td>
                            <td class="sl-price-cell">
                                <span class="fw-700" style="font-size:15px;padding:20px;color:var(--primary,#4f46e5);">
                                    {{ format_currency($groupAmt) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No items.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Terms / Guarantee / Payment (collapsible, like form page) --}}
        @php
            $termsSections = [
                ['id' => 'terms',     'label' => 'Terms & Conditions', 'icon' => 'bi-file-text',   'content' => $invoice->terms],
                ['id' => 'guarantee', 'label' => 'Guarantee',          'icon' => 'bi-patch-check', 'content' => $invoice->guarantee],
                ['id' => 'payment',   'label' => 'Payment Terms',      'icon' => 'bi-credit-card', 'content' => $invoice->payment],
            ];
        @endphp
        <div class="show-meta-card mb-0">
            <div class="meta-label mb-3"><i class="bi bi-journal-text me-1"></i>Terms & Policies</div>
            <div class="row g-3">
                @foreach($termsSections as $section)
                <div class="col-12 col-md-4">
                    <div class="terms-acc-card">
                        <button
                            type="button"
                            class="terms-acc-head"
                            data-bs-toggle="collapse"
                            data-bs-target="#terms-collapse-{{ $section['id'] }}"
                            aria-expanded="false"
                            aria-controls="terms-collapse-{{ $section['id'] }}"
                            @if(!filled($section['content'])) disabled style="cursor:default;opacity:.85;" @endif
                        >
                            <span class="terms-acc-icon"><i class="bi {{ $section['icon'] }}"></i></span>
                            <span class="terms-acc-title">{{ $section['label'] }}</span>
                           
                            @if(filled($section['content']))
                            <i class="bi bi-chevron-down terms-acc-chevron" aria-hidden="true"></i>
                            @endif
                        </button>
                        @if(filled($section['content']))
                        <div class="collapse" id="terms-collapse-{{ $section['id'] }}">
                            <div class="terms-acc-body">{{ $section['content'] }}</div>
                        </div>
                        @else
                        <div class="terms-acc-body terms-acc-empty">No content added.</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ── Right ── --}}
    <div class="col-12 col-lg-4">

        {{-- Customer --}}
        <div class="show-meta-card">
            <div class="meta-label mb-3">Bill To</div>
            <div class="fw-600" style="font-size:15px;">{{ $invoice->customer->name }}</div>
            @if($invoice->customer->company_name)
            <div class="text-muted" style="font-size:13px;">{{ $invoice->customer->company_name }}</div>
            @endif
            @if($invoice->customer->email)
            <div style="font-size:13px;margin-top:6px;"><i class="bi bi-envelope me-1 text-muted"></i>{{ $invoice->customer->email }}</div>
            @endif
            @if($invoice->customer->mobile)
            <div style="font-size:13px;"><i class="bi bi-phone me-1 text-muted"></i>{{ $invoice->customer->mobile }}</div>
            @endif
        </div>

        {{-- Totals --}}
        <div class="show-meta-card">
            <div class="meta-label mb-3">Summary</div>
            @if($invoice->quotation)
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted"><i class="bi bi-file-earmark-text me-1"></i>Quotation Total</span>
                <span class="fw-600 text-secondary text-decoration-line-through">{{ format_currency($invoice->quotation->total) }}</span>
            </div>
            @endif
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Subtotal</span>
                <span class="fw-600">{{ format_currency($invoice->subtotal) }}</span>
            </div>
            @if($invoice->discount > 0)
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Discount @if($invoice->discount_percent > 0)({{ $invoice->discount_percent }}%)@endif</span>
                <span class="fw-600 text-danger">−{{ format_currency($invoice->discount) }}</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Tax ({{ $invoice->tax_percent }}%)</span>
                <span class="fw-600 text-success">+{{ format_currency($invoice->tax_amount) }}</span>
            </div>
            @endif
            <div class="d-flex justify-content-between pt-3 {{ $invoice->quotation && ((float)$invoice->quotation->total - (float)$invoice->total > 0.01) ? 'border-bottom pb-2' : '' }}">
                <span style="font-size:16px;font-weight:800;">Total</span>
                <span style="font-size:18px;font-weight:800;color:var(--primary,#4f46e5);">{{ format_currency($invoice->total) }}</span>
            </div>
            @if($invoice->quotation && ((float)$invoice->quotation->total - (float)$invoice->total > 0.01))
            <div class="d-flex justify-content-between pt-2">
                <span class="text-success fw-700" style="font-size:12px;"><i class="bi bi-arrow-down-circle me-1"></i>Total Reduced</span>
                <span class="text-success fw-700" style="font-size:13px;">−{{ format_currency((float)$invoice->quotation->total - (float)$invoice->total) }}</span>
            </div>
            @endif
        </div>

        {{-- Account Balance --}}
        <div class="show-meta-card">
            <div class="meta-label mb-3">Payment & Expense Balance</div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Paid / Collected</span>
                <span class="fw-700 text-success">{{ format_currency($accountSummary['paid']) }}</span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Outstanding</span>
                <span class="fw-700 text-danger">{{ format_currency($accountSummary['outstanding']) }}</span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Invested Expense</span>
                <span class="fw-700 text-danger">-{{ format_currency($accountSummary['expense']) }}</span>
            </div>
            <div class="d-flex justify-content-between pt-3">
                <span style="font-size:15px;font-weight:800;">Net After Expense</span>
                <span class="fw-800 {{ $accountSummary['net_balance'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:17px;">
                    {{ format_currency($accountSummary['net_balance']) }}
                </span>
            </div>
            <div class="d-grid gap-2 mt-3">
                @if(auth()->user()->hasPermission('transactions.create'))
                <a href="{{ route('admin.transactions.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-cash-stack me-1"></i>Record Payment
                </a>
                @endif
                @if(auth()->user()->hasPermission('expenses.create'))
                <a href="{{ route('admin.expenses.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-wallet2 me-1"></i>Add Expense
                </a>
                @endif
            </div>
        </div>

        {{-- Meta --}}
        <div class="show-meta-card">
            <div class="meta-label mb-3">Details</div>
            @if($invoice->quotation)
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:12px;">Quotation</span>
                <a href="{{ route('admin.quotations.show', $invoice->quotation) }}" class="fw-600" style="font-size:13px;">
                    {{ $invoice->quotation->quotation_number }}
                </a>
            </div>
            @endif
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:12px;">Prepared By</span>
                <span class="fw-600" style="font-size:13px;">{{ $invoice->createdBy?->name ?? '—' }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:12px;">Created</span>
                <span style="font-size:13px;">{{ $invoice->created_at->format('d M Y') }}</span>
            </div>
            @if($invoice->updated_at->ne($invoice->created_at))
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:12px;">Last Updated</span>
                <span style="font-size:13px;">{{ $invoice->updated_at->format('d M Y') }}</span>
            </div>
            @endif
        </div>

    </div>
</div>

{{-- Email Modal --}}
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="emailModalLabel">
            <i class="bi bi-envelope-fill me-2 text-primary"></i>Send Invoice by Email
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-light border small mb-3" id="emailPdfHint">
            <i class="bi bi-paperclip me-1"></i>
            PDF attachment: <strong id="emailPdfDescLabel">With descriptions</strong>
            <button type="button" class="btn btn-link btn-sm p-0 ms-2" onclick="askDescThen(d => openEmailModal(d))">Change</button>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="emailTo">To (Email)</label>
          <input type="email" id="emailTo" class="form-control" value="{{ $invoice->customer->email }}" placeholder="customer@example.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="emailSubject">Subject</label>
          <input type="text" id="emailSubject" class="form-control" value="{{ $emailDefaults['subject'] }}">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="emailBody">Message</label>
          <textarea id="emailBody" class="form-control" rows="6">{{ $emailDefaults['body'] }}</textarea>
        </div>
        <div class="mb-0">
          <label class="form-label fw-semibold">PDF attachment</label>
          <div class="d-flex flex-wrap gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="emailDesc" id="emailDescYes" value="1" checked>
              <label class="form-check-label" for="emailDescYes">With description</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="emailDesc" id="emailDescNo" value="0">
              <label class="form-check-label" for="emailDescNo">Without description</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer flex-wrap gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btnSendEmail" onclick="submitSendEmail()">
          <i class="bi bi-send me-1"></i>Send Email
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const SEND_EMAIL_URL = '{{ route("admin.invoices.send-email", $invoice) }}';

function sendInvoiceEmail() {
    askDescThen(desc => openEmailModal(desc));
}

function askDescThen(callback) {
    Swal.fire({
        title: 'PDF for customer email',
        text: 'Include service descriptions in the attached PDF?',
        html: `<p class="text-muted small mb-3">Choose how the invoice PDF should be generated.</p>
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

function openEmailModal(showDesc) {
    const withDesc = String(showDesc) === '1';
    document.getElementById('emailDescYes').checked = withDesc;
    document.getElementById('emailDescNo').checked = !withDesc;
    document.getElementById('emailPdfDescLabel').textContent = withDesc ? 'With descriptions' : 'Without descriptions';

    const modalEl = document.getElementById('emailModal');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    if (!document.getElementById('emailTo').value.trim()) {
        setTimeout(() => document.getElementById('emailTo').focus(), 400);
    }
}

function submitSendEmail() {
    const btn = document.getElementById('btnSendEmail');
    const to = document.getElementById('emailTo').value.trim();
    const subj = document.getElementById('emailSubject').value.trim();
    const body = document.getElementById('emailBody').value.trim();
    const desc = document.querySelector('input[name="emailDesc"]:checked')?.value ?? '1';

    if (!to) { Swal.fire('Error', 'Please enter a recipient email.', 'error'); return; }
    if (!subj) { Swal.fire('Error', 'Please enter a subject.', 'error'); return; }
    if (!body) { Swal.fire('Error', 'Please enter a message.', 'error'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';

    $.post(SEND_EMAIL_URL, { to, subject: subj, body, show_desc: desc })
        .done(res => {
            bootstrap.Modal.getInstance(document.getElementById('emailModal'))?.hide();
            Swal.fire({ icon: 'success', title: 'Email sent', text: res.message, timer: 3500, showConfirmButton: false });
            if (res.badge) document.getElementById('statusBadgeWrap').innerHTML = res.badge;
        })
        .fail(x => {
            const msg = x.responseJSON?.message
                || (x.responseJSON?.errors ? Object.values(x.responseJSON.errors).flat().join(' ') : null)
                || 'Failed to send email. Check Settings -> Mail (SMTP).';
            Swal.fire('Error', msg, 'error');
        })
        .always(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i>Send Email';
        });
}

function changeStatus(status) {
    if (status === 'sent') {
        askDescThen(showDesc => postStatusChange(status, showDesc));
        return;
    }

    postStatusChange(status);
}

function postStatusChange(status, showDesc = null) {
    const payload = { status };
    if (showDesc !== null) payload.show_desc = showDesc;

    $.post('{{ route("admin.invoices.status", $invoice) }}', payload,
        r => {
            document.getElementById('statusBadgeWrap').innerHTML = r.badge;
            Swal.fire({ icon:'success', text:r.message, timer:1500, showConfirmButton:false });
        }
    ).fail(x => Swal.fire('Error', x.responseJSON?.message || 'Error', 'error'));
}

function duplicateInvoice(id) {
    Swal.fire({ 
        title:'Duplicate this invoice?', 
        icon:'question', 
        showCancelButton:true, 
        confirmButtonText:'Yes, duplicate!' 
    })
    .then(r => {
        if (!r.isConfirmed) return;
        const invoiceDuplicateUrl = id => '{{ route("admin.invoices.duplicate", ["invoice" => "__INVOICE__"]) }}'.replace('__INVOICE__', id);
        $.post(invoiceDuplicateUrl(id), {},
            res => Swal.fire({ icon:'success', text:res.message, timer:2000, showConfirmButton:false })
                       .then(() => window.location = res.redirect)
        ).fail(x => Swal.fire('Error', x.responseJSON?.message || 'Error', 'error'));
    });
}

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

document.querySelectorAll('input[name="emailDesc"]').forEach(el => {
    el.addEventListener('change', () => {
        const withDesc = document.getElementById('emailDescYes').checked;
        document.getElementById('emailPdfDescLabel').textContent = withDesc ? 'With descriptions' : 'Without descriptions';
    });
});
</script>
@endpush
