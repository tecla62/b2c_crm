@extends('layouts.app')
@section('title', $invoice ? 'Edit Invoice' : 'New Invoice')

@push('styles')
<style>
/* ── Base card ── */
.qform-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);margin-bottom:20px;}
.qform-section-title{
    font-size:12px;font-weight:800;letter-spacing:1px;text-transform:uppercase;
    color:#64748b;padding:14px 18px 10px;border-bottom:1px solid #f1f5f9;
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;
}

/* ── SL Group card ── */
.sl-group{
    border:1.5px solid #e2e8f0;border-radius:14px;
    margin-bottom:14px;overflow:hidden;background:#fff;
    transition:border-color .15s;
}
.sl-group:focus-within{border-color:var(--primary,#4f46e5);}

.sl-group-header{
    display:flex;align-items:center;gap:10px;
    padding:10px 14px;background:#f8fafc;
    border-bottom:1px solid #f1f5f9;cursor:pointer;user-select:none;
}
.sl-badge{
    width:28px;height:28px;border-radius:50%;
    background:var(--primary,#4f46e5);color:#fff;
    font-size:11px;font-weight:700;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.sl-group-title{font-size:13px;font-weight:600;flex:1;min-width:0;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.sl-price-badge{
    background:#e0e7ff;color:var(--primary,#4f46e5);
    border-radius:20px;padding:3px 10px;
    font-size:12px;font-weight:700;white-space:nowrap;flex-shrink:0;
}
.sl-toggle-icon{font-size:14px;color:#94a3b8;transition:transform .2s;flex-shrink:0;}
.sl-toggle-icon.open{transform:rotate(180deg);}
.btn-remove-sl{
    width:28px;height:28px;border-radius:8px;border:none;
    background:#fee2e2;color:#dc2626;font-size:14px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;flex-shrink:0;line-height:1;padding:0;
}
.btn-remove-sl:hover{background:#dc2626;color:#fff;}

.sl-group-body{padding:14px;display:none;}
.sl-group-body.open{display:block;}

/* ── Service rows ── */
.sl-svc-row{
    display:flex;align-items:flex-start;gap:8px;
    padding:8px 10px;border:1px solid #f1f5f9;
    border-radius:9px;margin-bottom:6px;background:#fafafa;
}
.sl-svc-row .svc-info{flex:1;min-width:0;}
.sl-svc-row .svc-title{font-size:13px;font-weight:600;color:#1e293b;}
.sl-svc-row .svc-cat{font-size:10px;color:#cbd5e1;white-space:nowrap;margin-top:1px;}
.svc-desc-preview{
    font-size:11px;color:#64748b;margin-top:2px;cursor:pointer;
    display:flex;align-items:center;gap:4px;
}
.svc-desc-preview .desc-text{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.svc-desc-preview .desc-text.empty{color:#cbd5e1;font-style:italic;}
.svc-desc-preview .btn-edit-desc{
    font-size:10px;color:#94a3b8;background:none;border:none;padding:0;line-height:1;
    flex-shrink:0;opacity:0;transition:opacity .15s;
}
.sl-svc-row:hover .btn-edit-desc{opacity:1;}
.btn-remove-svc{
    width:22px;height:22px;border-radius:6px;border:none;
    background:#fee2e2;color:#dc2626;font-size:12px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;flex-shrink:0;padding:0;margin-top:1px;
}
.btn-remove-svc:hover{background:#dc2626;color:#fff;}

/* ── Custom service form ── */
.custom-svc-form{
    background:#f8fafc;border:1.5px dashed #cbd5e1;border-radius:10px;
    padding:10px 12px;margin-bottom:10px;display:none;
}
.custom-svc-form.show{display:block;}
.btn-add-custom-svc{
    font-size:11px;font-weight:600;color:#4f46e5;background:none;border:none;
    padding:0;cursor:pointer;display:flex;align-items:center;gap:4px;margin-bottom:8px;
}
.btn-add-custom-svc:hover{text-decoration:underline;}

/* ── Inline service search ── */
.svc-inline-search{position:relative;margin-bottom:10px;}
.svc-search-results{
    border:1px solid #e2e8f0;border-radius:10px;
    max-height:200px;overflow-y:auto;
    background:#fff;box-shadow:0 4px 16px rgba(0,0,0,.1);
    display:none;z-index:300;position:absolute;
    left:0;right:0;top:calc(100% + 4px);
}
.svc-search-results.show{display:block;}
.svc-search-opt{
    padding:9px 12px;cursor:pointer;border-bottom:1px solid #f1f5f9;transition:background .1s;
}
.svc-search-opt:last-child{border-bottom:none;}
.svc-search-opt:hover{background:#f0f4ff;}
.svc-search-opt .opt-title{font-size:13px;font-weight:600;color:#1e293b;}
.svc-search-opt .opt-cat{font-size:11px;color:#94a3b8;}

/* ── Mode tabs ── */
.mode-tabs{display:flex;gap:4px;margin-bottom:12px;}
.mode-tab{
    flex:1;padding:8px 4px;border:1.5px solid #e2e8f0;border-radius:9px;
    background:#fff;font-size:12px;font-weight:600;color:#64748b;
    cursor:pointer;text-align:center;transition:all .15s;white-space:nowrap;
}
.mode-tab.active{border-color:var(--primary,#4f46e5);background:var(--primary,#4f46e5);color:#fff;}

/* ── Calc display ── */
.calc-display{
    background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;
    border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;
    text-align:center;margin-top:6px;
}
.calc-display.empty{display:none;}

/* ── Group total box ── */
.group-total-box{
    background:#e0e7ff;color:var(--primary,#4f46e5);
    border-radius:10px;padding:9px 14px;
    font-size:14px;font-weight:800;text-align:right;
    margin-top:12px;display:flex;align-items:center;justify-content:space-between;
}
.group-total-box .lbl{font-size:11px;font-weight:600;opacity:.75;}

/* ── Add SL button ── */
.btn-add-sl{
    width:100%;padding:11px;border:2px dashed #cbd5e1;
    border-radius:12px;background:transparent;
    font-size:13px;font-weight:600;color:#64748b;
    cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;
    transition:all .15s;margin-top:4px;
}
.btn-add-sl:hover{border-color:var(--primary,#4f46e5);color:var(--primary,#4f46e5);background:#f0f4ff;}

/* ── Totals box ── */
.totals-box{background:#f8fafc;border-radius:12px;padding:16px;}
.totals-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:13.5px;}
.totals-row.grand{border-top:2px solid #e2e8f0;margin-top:6px;padding-top:10px;font-size:16px;font-weight:800;color:var(--primary,#4f46e5);}

/* ── Category picker modal ── */
.cat-group-header{
    background:linear-gradient(135deg,var(--primary,#4f46e5),#818cf8);
    color:#fff;border-radius:10px;padding:10px 16px;
    font-weight:700;font-size:13px;margin-bottom:8px;
    display:flex;align-items:center;gap:8px;
}
.svc-pick-row{display:flex;align-items:center;gap:8px;padding:8px 6px;border-radius:8px;transition:background .1s;}
.svc-pick-row:hover{background:#f0f4ff;}

/* ── field label ── */
.field-label{font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;display:block;}

/* ── Terms block ── */
.terms-block{
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;
}
.terms-block .terms-label{
    font-size:13px;font-weight:600;color:#1e293b;
    display:flex;align-items:center;gap:6px;
}
.terms-block .terms-label i{color:var(--primary,#4f46e5);}
.btn-edit-terms{
    font-size:11px;color:var(--primary,#4f46e5);background:none;border:1px solid #e0e7ff;
    padding:3px 10px;border-radius:6px;cursor:pointer;font-weight:600;
    display:flex;align-items:center;gap:4px;flex-shrink:0;
}
.btn-edit-terms:hover{background:#e0e7ff;}

/* ── Floating mobile save bar ── */
.mob-save-bar{
    display:none;
    position:fixed;bottom:0;left:0;right:0;z-index:1050;
    background:#fff;border-top:1px solid #e2e8f0;
    padding:10px 16px;gap:10px;
    box-shadow:0 -4px 16px rgba(0,0,0,.10);
    align-items:center;
}
.mob-save-bar .mob-total{
    flex:1;font-size:15px;font-weight:800;color:var(--primary,#4f46e5);
}
.mob-save-bar .mob-total small{display:block;font-size:10px;font-weight:500;color:#94a3b8;}

/* ── Mobile FAB ── */
.mob-fab{
    display:none;
    position:fixed;bottom:72px;right:16px;z-index:1049;
    width:52px;height:52px;border-radius:50%;
    background:var(--primary,#4f46e5);color:#fff;
    border:none;font-size:22px;box-shadow:0 4px 16px rgba(79,70,229,.4);
    align-items:center;justify-content:center;cursor:pointer;
}

/* Quick add customer modal */
.quick-customer-popup{border-radius:18px;padding:0;overflow:hidden;}
.quick-customer-popup .swal2-html-container{margin:0;text-align:left;}
.quick-customer-popup .swal2-actions{
    margin:0;padding:14px 22px 20px;border-top:1px solid #f1f5f9;
    justify-content:flex-end;gap:10px;
}
.quick-customer-popup .swal2-confirm,
.quick-customer-popup .swal2-cancel{border-radius:9px;font-size:13px;font-weight:700;padding:9px 16px;}
.quick-customer{color:#1e293b;}
.quick-customer-head{
    display:flex;align-items:center;gap:12px;
    padding:20px 22px 16px;background:#f8fafc;border-bottom:1px solid #f1f5f9;
}
.quick-customer-icon{
    width:42px;height:42px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
    background:color-mix(in srgb,var(--primary,#4f46e5) 12%,white);
    color:var(--primary,#4f46e5);font-size:20px;
}
.quick-customer-title{font-size:17px;font-weight:800;color:#0f172a;line-height:1.2;margin:0;}
.quick-customer-sub{font-size:12px;color:#64748b;margin-top:3px;}
.quick-customer-body{padding:20px 22px 6px;}
.quick-customer-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px;}
.quick-field{display:flex;flex-direction:column;gap:6px;}
.quick-field.full{grid-column:1/-1;}
.quick-field label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#64748b;}
.quick-input-wrap{
    display:flex;align-items:center;border:1.5px solid #e2e8f0;border-radius:10px;
    background:#fff;transition:border-color .15s,box-shadow .15s;
}
.quick-input-wrap:focus-within{
    border-color:var(--primary,#4f46e5);
    box-shadow:0 0 0 3px color-mix(in srgb,var(--primary,#4f46e5) 14%,transparent);
}
.quick-input-wrap i{width:40px;text-align:center;color:#94a3b8;font-size:15px;flex-shrink:0;}
.quick-input{
    width:100%;border:0;outline:0;background:transparent;
    padding:10px 12px 10px 0;font-size:14px;color:#0f172a;min-width:0;
}
.quick-input::placeholder{color:#cbd5e1;}
.quick-customer-note{
    display:flex;align-items:flex-start;gap:8px;margin-top:14px;padding:10px 12px;
    border-radius:10px;background:#f0fdf4;color:#166534;font-size:12px;line-height:1.4;
}
.quick-customer-note i{font-size:14px;margin-top:1px;flex-shrink:0;}

@media(max-width:576px){
    .mob-save-bar{display:flex;}
    .mob-fab{display:flex;}
    body{padding-bottom:70px;}
    .mode-tab{font-size:11px;padding:10px 4px;min-height:44px;}
    .btn-remove-sl{width:36px;height:36px;font-size:16px;}
    .btn-remove-svc{width:32px;height:32px;font-size:14px;}
    .btn-add-custom-svc{font-size:13px;padding:6px 0;}
    .sl-group-header{padding:10px 12px;gap:8px;}
    .sl-group-body{padding:10px;}
    .qform-section-title{padding:12px 12px 8px;}
    .sl-group{margin-bottom:10px;border-radius:12px;}
    .qform-card{border-radius:12px;margin-bottom:14px;}
    .svc-search-results{max-height:240px;}
    .col-12.col-lg-4 .qform-card{position:static !important;}
    .btn-edit-desc{opacity:1 !important;}
    .form-control,.form-select{font-size:16px;}
    .quick-customer-popup{width:calc(100% - 24px) !important;}
    .quick-customer-head{padding:18px 16px 14px;}
    .quick-customer-body{padding:16px 16px 4px;}
    .quick-customer-grid{grid-template-columns:1fr;}
    .quick-customer-popup .swal2-actions{padding:12px 16px 18px;}
}
</style>
@endpush

@section('content')
@php $isEdit = (bool)$invoice; @endphp

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $isEdit ? 'Edit '.$invoice->invoice_number : 'New Invoice' }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Create' }}</li>
            </ol>
        </nav>
    </div>
</div>

<form method="POST"
      action="{{ $isEdit ? route('admin.invoices.update',$invoice) : route('admin.invoices.store') }}"
      id="invoiceForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row g-3">

        {{-- ── LEFT ── --}}
        <div class="col-12 col-lg-8">

            {{-- Customer & Meta --}}
            <div class="qform-card">
                <div class="qform-section-title">
                    <span><i class="bi bi-person me-2"></i>Invoice Details</span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-600">
                                <i class="bi bi-file-earmark-text text-primary me-1"></i>Link Quotation (Optional)
                            </label>
                            <select name="quotation_id" id="quotationSelect" class="form-select @error('quotation_id') is-invalid @enderror" onchange="onQuotationSelected(this.value)">
                                <option value="">— Direct Invoice (No Quotation) —</option>
                                @foreach($quotations as $q)
                                <option value="{{ $q->id }}"
                                    data-customer-id="{{ $q->customer_id }}"
                                    data-total="{{ $q->total }}"
                                    {{ old('quotation_id', $selectedQuotation?->id ?? $invoice?->quotation_id) == $q->id ? 'selected' : '' }}>
                                    {{ $q->quotation_number }} — {{ $q->customer?->name ?? 'Customer' }} ({{ format_currency($q->total) }})
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text" style="font-size:11px;color:#64748b;">
                                Selecting a quotation will auto-populate customer, line items & amounts.
                            </div>
                            @error('quotation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-600">Customer <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <select name="customer_id" id="customerSelect" class="form-select @error('customer_id') is-invalid @enderror" required>
                                    <option value="">— Select Customer —</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id', $selectedQuotation?->customer_id ?? $invoice?->customer_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}{{ $c->company_name ? ' ('.$c->company_name.')' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2"
                                        onclick="openQuickCustomer()" title="Add Customer">
                                    <i class="bi bi-person-plus"></i>
                                </button>
                            </div>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-6 col-sm-4">
                            <label class="form-label fw-600">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', $invoice?->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-6 col-sm-4">
                            <label class="form-label fw-600">Due Date</label>
                            <input type="date" name="due_date" id="dueDateInput" class="form-control"
                                   value="{{ old('due_date', $invoice?->due_date?->format('Y-m-d') ?? ($selectedQuotation?->valid_until?->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d'))) }}">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label fw-600">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['draft','sent','paid','partial','overdue','cancelled'] as $s)
                                <option value="{{ $s }}" {{ old('status', $invoice?->status ?? 'draft') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Linked quotation alert banner --}}
                        <div class="col-12" id="linkedQuotationAlert" style="{{ ($selectedQuotation || $invoice?->quotation) ? '' : 'display:none;' }}">
                            <div class="alert alert-info py-2 mb-0 d-flex align-items-center gap-2 flex-wrap" style="font-size:13px;border-radius:10px;">
                                <i class="bi bi-link-45deg fs-5 text-primary"></i>
                                <span>Linked to Quotation: <strong id="linkedQuotationNum">{{ $selectedQuotation?->quotation_number ?? $invoice?->quotation?->quotation_number }}</strong>
                                    — Original Quotation Total: <strong id="linkedQuotationTotal" class="text-primary">{{ format_currency($selectedQuotation?->total ?? $invoice?->quotation?->total ?? 0) }}</strong>
                                </span>
                                <span class="badge bg-white text-info border ms-1" style="font-size:11px;">You can adjust line items, decrease prices, or apply discounts below.</span>
                                @if($selectedQuotation || $invoice?->quotation)
                                <a id="linkedQuotationViewBtn" href="{{ route('admin.quotations.show', $selectedQuotation ?? $invoice->quotation) }}" target="_blank" class="btn btn-outline-info btn-sm ms-auto py-1 px-2" style="font-size:11px;">
                                    <i class="bi bi-eye me-1"></i>View Quotation
                                </a>
                                @else
                                <a id="linkedQuotationViewBtn" href="#" target="_blank" class="btn btn-outline-info btn-sm ms-auto py-1 px-2" style="display:none;font-size:11px;">
                                    <i class="bi bi-eye me-1"></i>View Quotation
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SL GROUPS ── --}}
            <div class="qform-card">
                <div class="qform-section-title">
                    <span><i class="bi bi-list-ol me-2"></i>Line Items</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSlGroup()">
                            <i class="bi bi-plus me-1"></i><span class="d-none d-sm-inline">Add SL Item</span><span class="d-sm-none">Add SL</span>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#catPickerModal">
                            <i class="bi bi-grid me-1"></i><span class="d-none d-sm-inline">Pick from Category</span><span class="d-sm-none">Category</span>
                        </button>
                    </div>
                </div>
                <div class="p-3">
                    <div id="slGroupsContainer"></div>
                    <div id="emptyGroupsMsg" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        No items yet. Click "Add SL Item" or "Pick from Category".
                    </div>
                    <div id="slEmptyLimitWarning" class="text-center text-danger py-4" style="display:none;">
                        <div style="display:inline-flex;align-items:center;gap:.75rem;">
                            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#fee2e2" stroke="#ef4444" stroke-width="1.5"/>
                                <path d="M9.5 9.5L14.5 14.5" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M14.5 9.5L9.5 14.5" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <div class="text-start">
                                <div class="fw-bold">Blank SL group not allowed</div>
                                <div class="small text-muted">Complete or remove the current blank item before adding another.</div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-add-sl" onclick="addSlGroup()">
                        <i class="bi bi-plus-circle"></i> Add SL Item
                    </button>
                </div>
            </div>

            {{-- Notes & Terms --}}
            <div class="qform-card">
                <div class="qform-section-title"><span><i class="bi bi-chat-text me-2"></i>Subject & Terms</span></div>
                <div class="p-3 row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600">Subject</label>
                        <input type="text" name="subject" class="form-control"
                               placeholder="e.g. Invoice for Waterproofing works…"
                               value="{{ old('subject', $invoice?->subject) }}">
                    </div>

                    @php
                    $termsFields = [
                        ['key'=>'terms',     'label'=>'Terms & Conditions', 'icon'=>'bi-file-text',    'default'=>$defaults['terms']],
                        ['key'=>'guarantee', 'label'=>'Guarantee',          'icon'=>'bi-patch-check',  'default'=>$defaults['guarantee']],
                        ['key'=>'payment',   'label'=>'Payment Terms',      'icon'=>'bi-credit-card',  'default'=>$defaults['payment']],
                    ];
                    @endphp

                    @foreach($termsFields as $tf)
                    @php $val = old($tf['key'], $invoice?->{$tf['key']} ?? $tf['default']); @endphp
                    <div class="col-12 col-sm-4">
                        <input type="hidden" name="{{ $tf['key'] }}" id="field-{{ $tf['key'] }}" value="{{ $val }}">
                        <div class="terms-block">
                            <div class="terms-label">
                                <i class="bi {{ $tf['icon'] }}"></i>
                                {{ $tf['label'] }}
                            </div>
                            <button type="button" class="btn-edit-terms"
                                    onclick="openTermsPopup('{{ $tf['key'] }}','{{ addslashes($tf['label']) }}')">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── RIGHT ── --}}
        <div class="col-12 col-lg-4">
            <div class="qform-card" style="position:sticky;top:80px;">
                <div class="qform-section-title"><span><i class="bi bi-calculator me-2"></i>Summary</span></div>
                <div class="p-3">
                    <div class="totals-box">
                        <div class="totals-row">
                            <span class="text-muted">Subtotal</span>
                            <span id="summSubtotal" class="fw-600">{{ format_currency(0) }}</span>
                        </div>
                        <div class="row g-2 my-1">
                            <div class="col-6">
                                <label class="field-label">Discount ({{ setting('currency_symbol', '₹') }})</label>
                                <input type="number" name="discount" id="discountAmount" class="form-control form-control-sm"
                                       min="0" step="0.01" placeholder="0.00"
                                       value="{{ old('discount', $invoice?->discount ?? ($selectedQuotation?->discount ?? 0)) }}"
                                       oninput="onDiscountAmountInput()">
                            </div>
                            <div class="col-6">
                                <label class="field-label">Discount %</label>
                                <input type="number" name="discount_percent" id="discountPct" class="form-control form-control-sm"
                                       min="0" max="100" step="0.01" placeholder="0%"
                                       value="{{ old('discount_percent', $invoice?->discount_percent ?? ($selectedQuotation?->discount_percent ?? 0)) }}"
                                       oninput="onDiscountPercentInput()">
                            </div>
                            <div class="col-12 mt-1">
                                <label class="field-label">Tax %</label>
                                @php
                                    $taxOptions = ['0','0.25','3','5','12','18','28'];
                                    $currentTax = old('tax_percent', $invoice?->tax_percent ?? ($selectedQuotation?->tax_percent ?? 0));
                                @endphp
                                <select name="tax_percent" id="taxPct" class="form-control form-control-sm" onchange="recalc()">
                                    @foreach($taxOptions as $t)
                                        <option value="{{ $t }}" @if((string)$currentTax === (string)$t) selected @endif>{{ $t }}%</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="totals-row" id="quotationBaseRow" style="{{ ($selectedQuotation || $invoice?->quotation) ? '' : 'display:none;' }}">
                            <span class="text-muted"><i class="bi bi-file-earmark-text me-1"></i>Quotation Total</span>
                            <span id="summQuotationTotal" class="text-secondary fw-600">{{ format_currency($selectedQuotation?->total ?? $invoice?->quotation?->total ?? 0) }}</span>
                        </div>
                        <div class="totals-row" id="discountRow" style="display:none;">
                            <span class="text-muted">Discount</span>
                            <span id="summDiscount" class="text-danger fw-600"></span>
                        </div>
                        <div class="totals-row" id="taxRow" style="display:none;">
                            <span class="text-muted">Tax</span>
                            <span id="summTax" class="text-success fw-600"></span>
                        </div>
                        <div class="totals-row grand">
                            <span>Total</span>
                            <span id="summTotal">{{ format_currency(0) }}</span>
                        </div>
                        <div class="totals-row" id="quotationDiffRow" style="display:none;">
                            <span class="text-success fw-600" style="font-size:12px;"><i class="bi bi-arrow-down-circle me-1"></i>Amount Reduced</span>
                            <span id="summQuotationSavings" class="text-success fw-700"></span>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>{{ $isEdit ? 'Update Invoice' : 'Save Invoice' }}
                        </button>
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x me-1"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

{{-- ── Mobile floating save bar ── --}}
<div class="mob-save-bar" id="mobSaveBar">
    <div class="mob-total">
        <small>Total</small>
        <span id="mobTotal">{{ setting('currency_symbol','₹') }}0.00</span>
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="addSlGroup()">
        <i class="bi bi-plus-lg me-1"></i>Add SL
    </button>
    <button type="submit" form="invoiceForm" class="btn btn-primary btn-sm px-3">
        <i class="bi bi-check-circle me-1"></i>Save
    </button>
</div>

{{-- ── Mobile FAB ── --}}
<button type="button" class="mob-fab" onclick="addSlGroup()" title="Add SL Item">
    <i class="bi bi-plus-lg"></i>
</button>

{{-- ── Terms Edit Popup ── --}}
<div class="modal fade" id="termsEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header py-3 px-4" style="border-bottom:1px solid #f1f5f9;">
                <h6 class="modal-title fw-700 mb-0" id="termsPopupLabel"></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <textarea id="termsPopupTextarea" class="form-control" rows="8"
                          style="resize:vertical;font-size:13px;"></textarea>
            </div>
            <div class="modal-footer px-4 py-3" style="border-top:1px solid #f1f5f9;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveTermsPopup()">
                    <i class="bi bi-check2 me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Description Edit Popup ── --}}
<div class="modal fade" id="descEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header py-3 px-4" style="border-bottom:1px solid #f1f5f9;">
                <h6 class="modal-title fw-700 mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Description</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="fw-600 mb-2" id="descPopupTitle" style="font-size:13px;color:#1e293b;"></div>
                <textarea id="descPopupTextarea" class="form-control" rows="5"
                          placeholder="Enter description…" style="resize:vertical;"></textarea>
            </div>
            <div class="modal-footer px-4 py-3" style="border-top:1px solid #f1f5f9;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveDescPopup()">
                    <i class="bi bi-check2 me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Category Picker Modal ── --}}
<div class="modal fade" id="catPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f1f5f9;padding:16px 20px;">
                <h5 class="modal-title fw-700">
                    <i class="bi bi-grid me-2 text-primary"></i>Select Services → Add as SL Group
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="alert alert-info py-2 mb-3" style="font-size:12px;">
                    <i class="bi bi-info-circle me-1"></i>
                    Each checked service will be added as its own fixed-price product line.
                </div>
                <input type="text" id="catSearchInput" class="form-control mb-3" placeholder="Search services…">
                <div id="catPickerBody">
                    @foreach($categories as $cat)
                    <div class="cat-group mb-3">
                        <div class="cat-group-header">
                            <i class="bi bi-tag me-1"></i>{{ $cat->name }}
                            <span class="ms-auto badge bg-white text-primary">{{ $cat->services->count() }}</span>
                        </div>
                        @foreach($cat->services as $svc)
                        <div class="svc-pick-row mb-1" data-name="{{ strtolower($svc->title) }}">
                            <input type="checkbox" class="form-check-input svc-checkbox" style="width:18px;height:18px;flex-shrink:0;"
                                   data-service-id="{{ $svc->id }}"
                                   data-category-id="{{ $cat->id }}"
                                   data-title="{{ $svc->title }}"
                                   data-desc="{{ $svc->description }}"
                                   data-cat-name="{{ $cat->name }}"
                                   data-price="{{ $svc->price ?? 0 }}">
                            <div class="flex-1" style="min-width:0;">
                                <div class="fw-600" style="font-size:13px;">{{ $svc->title }}</div>
                                @if($svc->description)
                                <div class="text-muted" style="font-size:11px;">{{ Str::limit($svc->description,60) }}</div>
                                @endif
                            </div>
                            <span class="text-muted" style="font-size:12px;white-space:nowrap;">{{ format_currency($svc->price ?? 0) }}</span>
                        </div>
                        @endforeach
                        @if($cat->services->isEmpty())
                        <div class="text-muted ps-2" style="font-size:12px;">No active services.</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:12px 20px;">
                <span class="text-muted me-auto" style="font-size:12px;" id="modalSelectedCount">0 selected</span>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addModalServicesAsGroup()">
                    <i class="bi bi-plus-circle me-1"></i>Add Product Lines
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ─────────────────────────────────────────────────────────────────────────────
// State
// ─────────────────────────────────────────────────────────────────────────────
const currSym  = '{{ setting("currency_symbol","₹") }}';
const IS_EDIT  = {{ $isEdit ? 'true' : 'false' }};
const LS_KEY   = 'invoice_draft';
let groupIdx   = 0;
let _lsSaveTimer = null;
let _currentQuotationTotal = {{ (float) ($selectedQuotation?->total ?? $invoice?->quotation?->total ?? 0) }};

@php
$allServicesData = $categories->flatMap(fn($cat) => $cat->services->map(fn($svc) => [
    'id'          => $svc->id,
    'title'       => $svc->title,
    'description' => $svc->description ?? '',
    'category_id' => $cat->id,
    'cat_name'    => $cat->name,
    'price'       => (float) ($svc->price ?? 0),
]));
@endphp
const ALL_SERVICES = @json($allServicesData);

// ─────────────────────────────────────────────────────────────────────────────
// Build SL group card HTML
// ─────────────────────────────────────────────────────────────────────────────
function buildGroupCard(gIdx, services = [], opts = {}) {
    const mode     = opts.mode        || 'fixed';
    const serviceTotal = services.reduce((sum, svc) => sum + (parseFloat(svc.price || 0) || 0), 0);
    const price    = opts.price ?? (serviceTotal || '');
    const sqft     = opts.area_value  || '';
    const rateSft  = opts.rate_per_sft|| '';
    const areaNos  = opts.area_nos    || '';
    const rateNos  = opts.rate_per_nos|| '';

    const svcsHtml = services.map((s, si) => buildSvcRow(gIdx, si, s)).join('');

    const titleText = services.length
        ? services.map(s => s.title).join(', ').substring(0, 55)
        : 'New SL Item';

    const card = document.createElement('div');
    card.className   = 'sl-group';
    card.id          = `slg-${gIdx}`;
    card.dataset.gidx = gIdx;

    card.innerHTML = `
        <div class="sl-group-header" onclick="toggleGroup(${gIdx})">
            <div class="sl-badge" id="slbadge-${gIdx}">?</div>
            <div class="sl-group-title" id="sltitle-${gIdx}">${esc(titleText)}</div>
            <div class="sl-price-badge" id="slprice-${gIdx}">${fmtCurr(0)}</div>
            <button type="button" class="btn-remove-sl" onclick="event.stopPropagation();removeGroup(${gIdx})" title="Remove">
                <i class="bi bi-trash"></i>
            </button>
            <i class="bi bi-chevron-down sl-toggle-icon open" id="sltoggle-${gIdx}"></i>
        </div>

        <div class="sl-group-body open" id="slbody-${gIdx}">

            <div class="sl-services-list mb-2" id="slsvclist-${gIdx}">${svcsHtml}</div>

            <button type="button" class="btn-add-custom-svc" onclick="toggleCustomForm(${gIdx})">
                <i class="bi bi-pencil-square"></i> Add Custom Service
            </button>
            <div class="custom-svc-form" id="slcustomform-${gIdx}">
                <div class="row g-2">
                    <div class="col-12 col-sm-5">
                        <input type="text" class="form-control form-control-sm" id="slcustomtitle-${gIdx}" placeholder="Service title *">
                    </div>
                    <div class="col-12 col-sm-5">
                        <input type="text" class="form-control form-control-sm" id="slcustomdesc-${gIdx}" placeholder="Description (optional)">
                    </div>
                    <div class="col-12 col-sm-2">
                        <button type="button" class="btn btn-primary btn-sm w-100"
                                onclick="addCustomSvc(${gIdx})">Add</button>
                    </div>
                </div>
            </div>

            <div class="svc-inline-search mb-3" id="slsearch-wrap-${gIdx}">
                <input type="text" class="form-control form-control-sm"
                       placeholder="&#xe8b6; Search & add a service…"
                       id="slsearch-${gIdx}" autocomplete="off"
                       oninput="filterInlineSearch(${gIdx},this.value)"
                       onfocus="showInlineSearch(${gIdx})"
                       onblur="hideInlineSearch(${gIdx})">
                <div class="svc-search-results" id="slresults-${gIdx}"></div>
            </div>

            <div class="mode-tabs" id="slmodetabs-${gIdx}">
                <button type="button" class="mode-tab ${mode==='fixed'   ?'active':''}"
                        onclick="setGroupMode(${gIdx},'fixed',this)">
                    <i class="bi bi-input-cursor-text me-1"></i>Fixed
                </button>
                <button type="button" class="mode-tab ${mode==='sqft'    ?'active':''}"
                        onclick="setGroupMode(${gIdx},'sqft',this)">
                    <i class="bi bi-bounding-box me-1"></i>SqFt × Rate
                </button>
                <button type="button" class="mode-tab ${mode==='nos'     ?'active':''}"
                        onclick="setGroupMode(${gIdx},'nos',this)">
                    <i class="bi bi-123 me-1"></i>Nos × Rate
                </button>
            </div>

            <input type="hidden" name="groups[${gIdx}][area_mode]"
                   id="slareamode-${gIdx}" value="${mode}">
            <input type="hidden" name="groups[${gIdx}][sl_no]"
                   id="slno-${gIdx}" value="0">

            <div id="slpanel-fixed-${gIdx}" class="${mode!=='fixed'?'d-none':''}">
                <label class="field-label">Amount (₹)</label>
                <input type="number" name="groups[${gIdx}][price]"
                       class="form-control"
                       min="0" step="0.01" placeholder="0.00"
                       value="${price}"
                       data-gidx="${gIdx}"
                       oninput="calcGroupTotal(${gIdx})">
            </div>

            <div id="slpanel-sqft-${gIdx}" class="${mode!=='sqft'?'d-none':''}">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="field-label">Total SqFt</label>
                        <input type="number" name="groups[${gIdx}][area_value]"
                               class="form-control"
                               min="0" step="0.01" placeholder="0.00"
                               value="${sqft}"
                               data-gidx="${gIdx}"
                               oninput="calcGroupTotal(${gIdx})">
                    </div>
                    <div class="col-6">
                        <label class="field-label">Rate per SqFt (₹)</label>
                        <input type="number" name="groups[${gIdx}][rate_per_sft]"
                               class="form-control"
                               min="0" step="0.01" placeholder="0.00"
                               value="${rateSft}"
                               data-gidx="${gIdx}"
                               oninput="calcGroupTotal(${gIdx})">
                    </div>
                </div>
                <div class="calc-display empty" id="slcalc-sqft-${gIdx}"></div>
                <div class="mt-2">
                    <label class="field-label text-muted">
                        Override Total (₹)
                        <span class="text-muted fw-normal ms-1" style="font-size:10px;">optional</span>
                    </label>
                    <input type="number" name="groups[${gIdx}][price_override]"
                           class="form-control override-price"
                           min="0" step="0.01" placeholder="Auto-calculated…"
                           value="${price}"
                           data-gidx="${gIdx}"
                           oninput="calcGroupTotal(${gIdx})">
                </div>
            </div>

            <div id="slpanel-nos-${gIdx}" class="${mode!=='nos'?'d-none':''}">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="field-label">No. of Units</label>
                        <input type="number" name="groups[${gIdx}][area_nos]"
                               class="form-control"
                               min="0" step="1" placeholder="0"
                               value="${areaNos}"
                               data-gidx="${gIdx}"
                               oninput="calcGroupTotal(${gIdx})">
                    </div>
                    <div class="col-6">
                        <label class="field-label">Rate per Unit (₹)</label>
                        <input type="number" name="groups[${gIdx}][rate_per_nos]"
                               class="form-control"
                               min="0" step="0.01" placeholder="0.00"
                               value="${rateNos}"
                               data-gidx="${gIdx}"
                               oninput="calcGroupTotal(${gIdx})">
                    </div>
                </div>
                <div class="calc-display empty" id="slcalc-nos-${gIdx}"></div>
                <div class="mt-2">
                    <label class="field-label text-muted">
                        Override Total (₹)
                        <span class="text-muted fw-normal ms-1" style="font-size:10px;">optional</span>
                    </label>
                    <input type="number" name="groups[${gIdx}][price_override]"
                           class="form-control override-price"
                           min="0" step="0.01" placeholder="Auto-calculated…"
                           value="${price}"
                           data-gidx="${gIdx}"
                           oninput="calcGroupTotal(${gIdx})">
                </div>
            </div>

            <div class="group-total-box" id="slgrouptotal-${gIdx}">
                <span class="lbl">Group Total</span>
                <span id="slgrouptotalval-${gIdx}">${fmtCurr(0)}</span>
            </div>

        </div>
    `;
    return card;
}

function buildSvcRow(gIdx, sIdx, svc) {
    const desc = svc.description || '';
    return `
        <div class="sl-svc-row" id="slsvc-${gIdx}-${sIdx}">
            <input type="hidden" name="groups[${gIdx}][services][${sIdx}][service_id]"  value="${svc.id || ''}">
            <input type="hidden" name="groups[${gIdx}][services][${sIdx}][category_id]" value="${svc.category_id || ''}">
            <input type="hidden" name="groups[${gIdx}][services][${sIdx}][title]"        value="${escAttr(svc.title || '')}">
            <input type="hidden" name="groups[${gIdx}][services][${sIdx}][description]"  id="slsvcdesc-hidden-${gIdx}-${sIdx}" value="${escAttr(desc)}">
            <input type="hidden" name="groups[${gIdx}][services][${sIdx}][price]" value="${parseFloat(svc.price || 0) || 0}">
            <div class="svc-info">
                <div class="svc-title">${esc(svc.title || 'Unnamed')}</div>
                <div class="svc-desc-preview" onclick="openDescPopup('${gIdx}','${sIdx}')">
                    <span class="desc-text ${desc ? '' : 'empty'}" id="slsvcdesc-preview-${gIdx}-${sIdx}">${desc ? esc(desc) : 'Add description…'}</span>
                    <button type="button" class="btn-edit-desc" tabindex="-1"><i class="bi bi-pencil"></i></button>
                </div>
                ${svc.cat_name ? `<div class="svc-cat">${esc(svc.cat_name)}</div>` : ''}
            </div>
            <button type="button" class="btn-remove-svc"
                    onclick="removeSvcFromGroup(${gIdx},${sIdx})"
                    title="Remove">
                <i class="bi bi-x"></i>
            </button>
        </div>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// Group management
// ─────────────────────────────────────────────────────────────────────────────
function countEmptySlGroups() {
    return Array.from(document.querySelectorAll('.sl-group')).reduce((count, card) => {
        const gIdx = card.dataset.gidx;
        const services = [];
        card.querySelectorAll('.sl-svc-row').forEach(row => {
            services.push({
                id:          row.querySelector('[name$="[service_id]"]')?.value || '',
                title:       row.querySelector('[name$="[title]"]')?.value || '',
                description: row.querySelector('[name$="[description]"]')?.value || '',
                price:       document.querySelector(`#slpanel-fixed-${gIdx} [name$="[price]"]`)?.value || '',
            });
        });

        const group = {
            price:        document.querySelector(`#slpanel-fixed-${gIdx} [name$="[price]"]`)?.value || '',
            area_value:   document.querySelector(`[name="groups[${gIdx}][area_value]"]`)?.value || '',
            rate_per_sft: document.querySelector(`[name="groups[${gIdx}][rate_per_sft]"]`)?.value || '',
            area_nos:     document.querySelector(`[name="groups[${gIdx}][area_nos]"]`)?.value || '',
            rate_per_nos: document.querySelector(`[name="groups[${gIdx}][rate_per_nos]"]`)?.value || '',
            services:     services,
        };

        return count + (!hasValidDraftGroup(group) ? 1 : 0);
    }, 0);
}

function showEmptyGroupWarning() {
    const el = document.getElementById('slEmptyLimitWarning');
    if (!el) return;
    el.style.display = 'block';
    setTimeout(() => { if (el) el.style.display = 'none'; }, 3600);
}

function isValidDraftServiceRow(svc) {
    return svc?.id?.toString().trim()
        || svc?.title?.toString().trim()
        || svc?.description?.toString().trim()
        || parseFloat(svc?.price || 0) > 0;
}

function hasValidDraftGroup(group) {
    return group.services.some(isValidDraftServiceRow)
        || parseFloat(group.price || 0) > 0
        || parseFloat(group.area_value || 0) > 0
        || parseFloat(group.rate_per_sft || 0) > 0
        || parseFloat(group.area_nos || 0) > 0
        || parseFloat(group.rate_per_nos || 0) > 0;
}

function addSlGroup(services = [], opts = {}, skipSave = false) {
    if (!services.length && countEmptySlGroups() >= 1) {
        showEmptyGroupWarning();
        return;
    }

    const gIdx = groupIdx++;
    const card = buildGroupCard(gIdx, services, opts);
    document.getElementById('slGroupsContainer').appendChild(card);
    document.getElementById('emptyGroupsMsg').style.display = 'none';
    reNumberGroups();
    calcGroupTotal(gIdx);
    if (window.innerWidth <= 576) {
        document.querySelectorAll('.sl-group').forEach(c => {
            if (c !== card) {
                const b = document.getElementById(`slbody-${c.dataset.gidx}`);
                const i = document.getElementById(`sltoggle-${c.dataset.gidx}`);
                if (b) { b.classList.remove('open'); b.style.display = 'none'; }
                if (i) i.classList.remove('open');
            }
        });
    }
    card.scrollIntoView({ behavior:'smooth', block:'nearest' });
    if (!skipSave) scheduleSave();
}

function removeGroup(gIdx) {
    document.getElementById(`slg-${gIdx}`)?.remove();
    reNumberGroups();
    recalc();
    if (!document.querySelectorAll('.sl-group').length)
        document.getElementById('emptyGroupsMsg').style.display = '';
    scheduleSave();
}

function toggleGroup(gIdx) {
    const body = document.getElementById(`slbody-${gIdx}`);
    const icon = document.getElementById(`sltoggle-${gIdx}`);
    const open = body.classList.contains('open');
    body.classList.toggle('open', !open);
    body.style.display = open ? 'none' : 'block';
    icon.classList.toggle('open', !open);
}

function reNumberGroups() {
    document.querySelectorAll('.sl-group').forEach((card, idx) => {
        const gIdx = card.dataset.gidx;
        const badge = document.getElementById(`slbadge-${gIdx}`);
        if (badge) badge.textContent = idx + 1;
        const slNoInput = document.getElementById(`slno-${gIdx}`);
        if (slNoInput) slNoInput.value = idx + 1;
    });
}

function addSvcToGroup(gIdx, svc) {
    const list = document.getElementById(`slsvclist-${gIdx}`);
    if (!list) return;
    const existing = Array.from(list.querySelectorAll('[name$="[service_id]"]'))
                          .map(el => String(el.value));
    if (svc.id && existing.includes(String(svc.id))) return;
    const sIdx = list.querySelectorAll('.sl-svc-row').length;
    const fixedPrice = document.querySelector(`#slpanel-fixed-${gIdx} [name="groups[${gIdx}][price]"]`);
    if (fixedPrice && !fixedPrice.value && svc.price) fixedPrice.value = svc.price;
    const tmp  = document.createElement('div');
    tmp.innerHTML = buildSvcRow(gIdx, sIdx, svc);
    list.appendChild(tmp.firstElementChild);
    updateGroupTitle(gIdx);
    scheduleSave();
}

function removeSvcFromGroup(gIdx, sIdx) {
    document.getElementById(`slsvc-${gIdx}-${sIdx}`)?.remove();
    updateGroupTitle(gIdx);
    recalc();
    scheduleSave();
}

function updateGroupTitle(gIdx) {
    const list   = document.getElementById(`slsvclist-${gIdx}`);
    const titles = Array.from(list?.querySelectorAll('[name$="[title]"]') || [])
                        .map(el => el.value).filter(Boolean);
    const el = document.getElementById(`sltitle-${gIdx}`);
    if (el) {
        const txt = titles.join(', ');
        el.textContent = txt.length > 55 ? txt.substring(0, 55) + '…' : (txt || 'New SL Item');
    }
}

const _hideTimers = {};

function filterInlineSearch(gIdx, q) {
    const results = document.getElementById(`slresults-${gIdx}`);
    if (!q.trim()) { results.classList.remove('show'); results.innerHTML = ''; return; }
    const matches = ALL_SERVICES.filter(s =>
        s.title.toLowerCase().includes(q.toLowerCase()) ||
        s.cat_name.toLowerCase().includes(q.toLowerCase())
    ).slice(0, 12);
    results.innerHTML = matches.length
        ? matches.map(s => `
            <div class="svc-search-opt"
                 onmousedown="event.preventDefault();addSvcToGroup(${gIdx},${JSON.stringify(s).replace(/"/g,'&quot;')});clearInlineSearch(${gIdx})">
                <div class="opt-title">${esc(s.title)}</div>
                <div class="opt-cat">${esc(s.cat_name)}</div>
            </div>`).join('')
        : '<div class="svc-search-opt"><div class="opt-cat">No results</div></div>';
    results.classList.add('show');
}

function showInlineSearch(gIdx) {
    clearTimeout(_hideTimers[gIdx]);
    const inp = document.getElementById(`slsearch-${gIdx}`);
    if (inp?.value.trim()) filterInlineSearch(gIdx, inp.value);
}

function hideInlineSearch(gIdx) {
    _hideTimers[gIdx] = setTimeout(() => {
        document.getElementById(`slresults-${gIdx}`)?.classList.remove('show');
    }, 200);
}

function toggleCustomForm(gIdx) {
    const form = document.getElementById(`slcustomform-${gIdx}`);
    form?.classList.toggle('show');
    if (form?.classList.contains('show'))
        document.getElementById(`slcustomtitle-${gIdx}`)?.focus();
}

function addCustomSvc(gIdx) {
    const titleEl = document.getElementById(`slcustomtitle-${gIdx}`);
    const descEl  = document.getElementById(`slcustomdesc-${gIdx}`);
    const title   = titleEl?.value.trim();
    if (!title) { titleEl?.focus(); return; }
    addSvcToGroup(gIdx, { id: '', category_id: '', title, description: descEl?.value.trim(), cat_name: '', price: 0 });
    titleEl.value = '';
    descEl.value  = '';
    document.getElementById(`slcustomform-${gIdx}`)?.classList.remove('show');
}

function clearInlineSearch(gIdx) {
    const inp = document.getElementById(`slsearch-${gIdx}`);
    const res = document.getElementById(`slresults-${gIdx}`);
    if (inp) inp.value = '';
    if (res) { res.classList.remove('show'); res.innerHTML = ''; }
}

function setGroupMode(gIdx, mode, btn) {
    document.querySelectorAll(`#slmodetabs-${gIdx} .mode-tab`)
            .forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    const modeInput = document.getElementById(`slareamode-${gIdx}`);
    if (modeInput) modeInput.value = mode;
    ['fixed','sqft','nos'].forEach(m => {
        document.getElementById(`slpanel-${m}-${gIdx}`)
                ?.classList.toggle('d-none', m !== mode);
    });
    calcGroupTotal(gIdx);
    scheduleSave();
}

function calcGroupTotal(gIdx) {
    const mode = document.getElementById(`slareamode-${gIdx}`)?.value || 'fixed';
    let computed = 0;

    if (mode === 'fixed') {
        computed = parseFloat(
            document.querySelector(`#slpanel-fixed-${gIdx} [name="groups[${gIdx}][price]"]`)?.value
        ) || 0;
    } else if (mode === 'sqft') {
        const sqft = parseFloat(
            document.querySelector(`[name="groups[${gIdx}][area_value]"]`)?.value
        ) || 0;
        const rate = parseFloat(
            document.querySelector(`[name="groups[${gIdx}][rate_per_sft]"]`)?.value
        ) || 0;
        computed = sqft * rate;
        const calcEl = document.getElementById(`slcalc-sqft-${gIdx}`);
        if (calcEl) {
            if (sqft > 0 && rate > 0) {
                calcEl.textContent = `${sqft.toLocaleString()} sqft × ${fmtCurr(rate)} = ${fmtCurr(computed)}`;
                calcEl.classList.remove('empty');
            } else {
                calcEl.classList.add('empty');
            }
        }
        const overrideEl = document.querySelector(`#slpanel-sqft-${gIdx} .override-price`);
        const overrideVal = parseFloat(overrideEl?.value) || 0;
        if (overrideVal > 0) computed = overrideVal;
    } else if (mode === 'nos') {
        const nos  = parseFloat(
            document.querySelector(`[name="groups[${gIdx}][area_nos]"]`)?.value
        ) || 0;
        const rate = parseFloat(
            document.querySelector(`[name="groups[${gIdx}][rate_per_nos]"]`)?.value
        ) || 0;
        computed = nos * rate;
        const calcEl = document.getElementById(`slcalc-nos-${gIdx}`);
        if (calcEl) {
            if (nos > 0 && rate > 0) {
                calcEl.textContent = `${nos} nos × ${fmtCurr(rate)} = ${fmtCurr(computed)}`;
                calcEl.classList.remove('empty');
            } else {
                calcEl.classList.add('empty');
            }
        }
        const overrideEl = document.querySelector(`#slpanel-nos-${gIdx} .override-price`);
        const overrideVal = parseFloat(overrideEl?.value) || 0;
        if (overrideVal > 0) computed = overrideVal;
    }

    document.getElementById(`slgrouptotalval-${gIdx}`).textContent = fmtCurr(computed);
    document.getElementById(`slprice-${gIdx}`).textContent          = fmtCurr(computed);
    recalc();
}

function getSubtotal() {
    let subtotal = 0;
    document.querySelectorAll('.sl-group').forEach(card => {
        const gIdx = card.dataset.gidx;
        const mode = document.getElementById(`slareamode-${gIdx}`)?.value || 'fixed';
        let g = 0;
        if (mode === 'fixed') {
            g = parseFloat(
                document.querySelector(`#slpanel-fixed-${gIdx} [name="groups[${gIdx}][price]"]`)?.value
            ) || 0;
        } else if (mode === 'sqft') {
            const sqft = parseFloat(document.querySelector(`[name="groups[${gIdx}][area_value]"]`)?.value) || 0;
            const rate = parseFloat(document.querySelector(`[name="groups[${gIdx}][rate_per_sft]"]`)?.value) || 0;
            g = sqft * rate;
            const ov  = parseFloat(document.querySelector(`#slpanel-sqft-${gIdx} .override-price`)?.value) || 0;
            if (ov > 0) g = ov;
        } else if (mode === 'nos') {
            const nos  = parseFloat(document.querySelector(`[name="groups[${gIdx}][area_nos]"]`)?.value) || 0;
            const rate = parseFloat(document.querySelector(`[name="groups[${gIdx}][rate_per_nos]"]`)?.value) || 0;
            g = nos * rate;
            const ov  = parseFloat(document.querySelector(`#slpanel-nos-${gIdx} .override-price`)?.value) || 0;
            if (ov > 0) g = ov;
        }
        subtotal += g;
    });
    return subtotal;
}

function onDiscountAmountInput() {
    const subtotal = getSubtotal();
    const discAmtVal = document.getElementById('discountAmount')?.value;
    const discAmt = parseFloat(discAmtVal) || 0;
    const discPctEl = document.getElementById('discountPct');
    if (subtotal > 0 && discAmt > 0) {
        discPctEl.value = ((discAmt / subtotal) * 100).toFixed(2);
    } else if (!discAmtVal) {
        discPctEl.value = '';
    }
    recalc();
    scheduleSave();
}

function onDiscountPercentInput() {
    const subtotal = getSubtotal();
    const discPctVal = document.getElementById('discountPct')?.value;
    const discPct = parseFloat(discPctVal) || 0;
    const discAmtEl = document.getElementById('discountAmount');
    if (subtotal > 0 && discPct > 0) {
        discAmtEl.value = ((subtotal * discPct) / 100).toFixed(2);
    } else if (!discPctVal) {
        discAmtEl.value = '';
    }
    recalc();
    scheduleSave();
}

function recalc() {
    const subtotal = getSubtotal();
    const discAmtInput = document.getElementById('discountAmount');
    const discPctInput = document.getElementById('discountPct');
    const taxPctInput  = document.getElementById('taxPct');

    let disc = 0;
    const discAmtVal = parseFloat(discAmtInput?.value) || 0;
    const discPctVal = parseFloat(discPctInput?.value) || 0;

    if (discAmtVal > 0) {
        disc = Math.min(discAmtVal, subtotal);
    } else if (discPctVal > 0) {
        disc = subtotal * (discPctVal / 100);
    }

    const taxPct = parseFloat(taxPctInput?.value) || 0;
    const taxable = Math.max(0, subtotal - disc);
    const tax = taxable * (taxPct / 100);
    const total = taxable + tax;

    document.getElementById('summSubtotal').textContent   = fmtCurr(subtotal);
    document.getElementById('summDiscount').textContent   = '−' + fmtCurr(disc);
    document.getElementById('summTax').textContent        = '+' + fmtCurr(tax);
    document.getElementById('summTotal').textContent      = fmtCurr(total);
    document.getElementById('discountRow').style.display  = disc > 0 ? '' : 'none';
    document.getElementById('taxRow').style.display       = taxPct > 0 ? '' : 'none';
    const mobEl = document.getElementById('mobTotal');
    if (mobEl) mobEl.textContent = fmtCurr(total);

    // Quotation Comparison / Savings row
    const qDiffRow = document.getElementById('quotationDiffRow');
    if (_currentQuotationTotal > 0) {
        const diff = _currentQuotationTotal - total;
        if (qDiffRow) {
            if (diff > 0.01) {
                qDiffRow.style.display = '';
                document.getElementById('summQuotationSavings').innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-arrow-down-short"></i>Reduced by ${fmtCurr(diff)}</span>`;
            } else if (diff < -0.01) {
                qDiffRow.style.display = '';
                document.getElementById('summQuotationSavings').innerHTML = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><i class="bi bi-arrow-up-short"></i>+${fmtCurr(Math.abs(diff))}</span>`;
            } else {
                qDiffRow.style.display = 'none';
            }
        }
    } else if (qDiffRow) {
        qDiffRow.style.display = 'none';
    }
}

function onQuotationSelected(qId) {
    if (!qId) {
        _currentQuotationTotal = 0;
        const banner = document.getElementById('linkedQuotationAlert');
        if (banner) banner.style.display = 'none';
        const qBaseRow = document.getElementById('quotationBaseRow');
        if (qBaseRow) qBaseRow.style.display = 'none';
        const qDiffRow = document.getElementById('quotationDiffRow');
        if (qDiffRow) qDiffRow.style.display = 'none';
        recalc();
        scheduleSave();
        return;
    }

    // Fetch quotation details via AJAX
    const url = '{{ route("admin.invoices.quotation-data", ":id") }}'.replace(':id', qId);
    Swal.fire({
        title: 'Loading Quotation...',
        text: 'Fetching items and details',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(url)
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (!data.success) {
                Swal.fire('Error', data.message || 'Failed to load quotation data', 'error');
                return;
            }

            _currentQuotationTotal = parseFloat(data.total || 0);

            // Set customer
            if (data.customer_id) {
                const custSelect = document.getElementById('customerSelect') || document.querySelector('[name="customer_id"]');
                if (custSelect) custSelect.value = data.customer_id;
            }

            // Set subject & terms
            if (data.subject !== undefined) document.querySelector('[name="subject"]').value = data.subject || '';
            if (data.terms !== undefined) document.getElementById('field-terms').value = data.terms || '';
            if (data.guarantee !== undefined) document.getElementById('field-guarantee').value = data.guarantee || '';
            if (data.payment !== undefined) document.getElementById('field-payment').value = data.payment || '';

            // Set tax & discount
            if (data.tax_percent !== undefined) {
                const taxEl = document.getElementById('taxPct');
                if (taxEl) taxEl.value = data.tax_percent;
            }
            if (data.discount !== undefined) {
                const discAmtEl = document.getElementById('discountAmount');
                if (discAmtEl) discAmtEl.value = data.discount;
            }
            if (data.discount_percent !== undefined) {
                const discPctEl = document.getElementById('discountPct');
                if (discPctEl) discPctEl.value = data.discount_percent;
            }

            // Update banner
            const banner = document.getElementById('linkedQuotationAlert');
            if (banner) {
                banner.style.display = '';
                const numEl = document.getElementById('linkedQuotationNum');
                if (numEl) numEl.textContent = data.quotation_number;
                const totEl = document.getElementById('linkedQuotationTotal');
                if (totEl) totEl.textContent = fmtCurr(_currentQuotationTotal);
                const viewBtn = document.getElementById('linkedQuotationViewBtn');
                if (viewBtn) {
                    viewBtn.href = '{{ url("admin/quotations") }}/' + data.id;
                    viewBtn.style.display = '';
                }
            }

            // Update quotation base row in totals
            const qBaseRow = document.getElementById('quotationBaseRow');
            if (qBaseRow) {
                qBaseRow.style.display = '';
                const summQTot = document.getElementById('summQuotationTotal');
                if (summQTot) summQTot.textContent = fmtCurr(_currentQuotationTotal);
            }

            // Populate groups
            const container = document.getElementById('slGroupsContainer');
            container.innerHTML = '';
            groupIdx = 0;

            if (data.groups && data.groups.length > 0) {
                data.groups.forEach(g => {
                    addSlGroup(g.services || [], g, true);
                });
            } else {
                document.getElementById('emptyGroupsMsg').style.display = '';
            }

            recalc();
            scheduleSave();

            Swal.fire({
                icon: 'success',
                title: 'Quotation Loaded',
                text: `Items loaded from ${data.quotation_number} (${fmtCurr(_currentQuotationTotal)}). You can now apply discounts or modify amounts.`,
                timer: 2000,
                showConfirmButton: false
            });
        })
        .catch(err => {
            Swal.close();
            console.error(err);
            Swal.fire('Error', 'Failed to fetch quotation details', 'error');
        });
}

function addModalServicesAsGroup() {
    const checked = document.querySelectorAll('.svc-checkbox:checked');
    if (!checked.length) {
        Swal.fire({icon:'warning',text:'Select at least one service.',timer:1500,showConfirmButton:false});
        return;
    }
    const services = Array.from(checked).map(cb => ({
        id:          cb.dataset.serviceId,
        category_id: cb.dataset.categoryId,
        title:       cb.dataset.title,
        description: cb.dataset.desc,
        cat_name:    cb.dataset.catName,
        price:       cb.dataset.price || 0,
    }));
    services.forEach(svc => addSlGroup([svc], { mode: 'fixed', price: svc.price || 0 }));
    checked.forEach(cb => cb.checked = false);
    document.getElementById('modalSelectedCount').textContent = '0 selected';
    bootstrap.Modal.getInstance(document.getElementById('catPickerModal')).hide();
}

document.querySelectorAll('.svc-checkbox').forEach(cb =>
    cb.addEventListener('change', () => {
        const n = document.querySelectorAll('.svc-checkbox:checked').length;
        document.getElementById('modalSelectedCount').textContent = n + ' selected';
    })
);

document.getElementById('catSearchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.svc-pick-row').forEach(r => {
        r.style.display = r.dataset.name.includes(q) ? '' : 'none';
    });
});

let _termsPopupKey = null;

function openTermsPopup(fieldKey, label) {
    _termsPopupKey = fieldKey;
    const current = document.getElementById(`field-${fieldKey}`)?.value || '';
    document.getElementById('termsPopupLabel').innerHTML =
        `<i class="bi bi-pencil-square me-2 text-primary"></i>${label}`;
    document.getElementById('termsPopupTextarea').value = current;
    const el = document.getElementById('termsEditModal');
    (bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el)).show();
    setTimeout(() => document.getElementById('termsPopupTextarea')?.focus(), 350);
}

function saveTermsPopup() {
    if (!_termsPopupKey) return;
    const val    = document.getElementById('termsPopupTextarea').value.trim();
    const hidden = document.getElementById(`field-${_termsPopupKey}`);
    if (hidden) hidden.value = val;
    bootstrap.Modal.getInstance(document.getElementById('termsEditModal')).hide();
    scheduleSave();
}

let _descPopupTarget = null;

function openDescPopup(gIdx, sIdx) {
    _descPopupTarget = { gIdx, sIdx };
    const title   = document.querySelector(`#slsvc-${gIdx}-${sIdx} .svc-title`)?.textContent || '';
    const current = document.getElementById(`slsvcdesc-hidden-${gIdx}-${sIdx}`)?.value || '';
    document.getElementById('descPopupTitle').textContent = title;
    document.getElementById('descPopupTextarea').value    = current;
    const el = document.getElementById('descEditModal');
    (bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el)).show();
    setTimeout(() => document.getElementById('descPopupTextarea')?.focus(), 350);
}

function saveDescPopup() {
    if (!_descPopupTarget) return;
    const { gIdx, sIdx } = _descPopupTarget;
    const val = document.getElementById('descPopupTextarea').value.trim();
    const hidden  = document.getElementById(`slsvcdesc-hidden-${gIdx}-${sIdx}`);
    const preview = document.getElementById(`slsvcdesc-preview-${gIdx}-${sIdx}`);
    if (hidden)  hidden.value = val;
    if (preview) {
        preview.textContent = val || 'Add description…';
        preview.classList.toggle('empty', !val);
    }
    bootstrap.Modal.getInstance(document.getElementById('descEditModal')).hide();
    scheduleSave();
}

function scheduleSave() {
    if (IS_EDIT) return;
    clearTimeout(_lsSaveTimer);
    _lsSaveTimer = setTimeout(saveDraft, 400);
}

function saveDraft() {
    if (IS_EDIT) return;
    const draft = {
        quotation_id:     document.getElementById('quotationSelect')?.value || '',
        customer_id:      document.querySelector('[name="customer_id"]')?.value || '',
        date:             document.querySelector('[name="date"]')?.value || '',
        due_date:         document.querySelector('[name="due_date"]')?.value || '',
        status:           document.querySelector('[name="status"]')?.value || '',
        discount:         document.getElementById('discountAmount')?.value || '',
        discount_percent: document.getElementById('discountPct')?.value || '',
        tax_percent:      document.getElementById('taxPct')?.value || '',
        subject:          document.querySelector('[name="subject"]')?.value || '',
        terms:            document.querySelector('[name="terms"]')?.value || '',
        guarantee:        document.querySelector('[name="guarantee"]')?.value || '',
        payment:          document.querySelector('[name="payment"]')?.value || '',
        groups: [],
    };
    document.querySelectorAll('.sl-group').forEach(card => {
        const gIdx = card.dataset.gidx;
        const mode = document.getElementById(`slareamode-${gIdx}`)?.value || 'fixed';
        const services = [];
        card.querySelectorAll('.sl-svc-row').forEach(row => {
            services.push({
                id:          row.querySelector('[name$="[service_id]"]')?.value || '',
                category_id: row.querySelector('[name$="[category_id]"]')?.value || '',
                title:       row.querySelector('[name$="[title]"]')?.value || '',
                description: row.querySelector('[name$="[description]"]')?.value || '',
                cat_name:    row.querySelector('.svc-cat')?.textContent || '',
                price:       document.querySelector(`#slpanel-fixed-${gIdx} [name$="[price]"]`)?.value || '',
            });
        });
        const group = {
            mode,
            price:        document.querySelector(`#slpanel-fixed-${gIdx} [name$="[price]"]`)?.value || '',
            area_value:   document.querySelector(`[name="groups[${gIdx}][area_value]"]`)?.value || '',
            rate_per_sft: document.querySelector(`[name="groups[${gIdx}][rate_per_sft]"]`)?.value || '',
            area_nos:     document.querySelector(`[name="groups[${gIdx}][area_nos]"]`)?.value || '',
            rate_per_nos: document.querySelector(`[name="groups[${gIdx}][rate_per_nos]"]`)?.value || '',
            services,
        };
        if (hasValidDraftGroup(group)) {
            draft.groups.push(group);
        }
    });
    localStorage.setItem(LS_KEY, JSON.stringify(draft));
}

function restoreDraft() {
    if (IS_EDIT) return;
    const raw = localStorage.getItem(LS_KEY);
    if (!raw) return;
    try {
        const draft = JSON.parse(raw);
        if (draft.quotation_id && document.getElementById('quotationSelect')) {
            document.getElementById('quotationSelect').value = draft.quotation_id;
        }
        const sel = document.querySelector('[name="customer_id"]');
        if (draft.customer_id && sel) sel.value = draft.customer_id;
        if (draft.date)             document.querySelector('[name="date"]').value             = draft.date;
        if (draft.due_date)         document.querySelector('[name="due_date"]').value         = draft.due_date;
        if (draft.status)           document.querySelector('[name="status"]').value           = draft.status;
        if (draft.discount)         document.getElementById('discountAmount').value          = draft.discount;
        if (draft.discount_percent) document.getElementById('discountPct').value             = draft.discount_percent;
        if (draft.tax_percent)      document.getElementById('taxPct').value                  = draft.tax_percent;
        if (draft.subject)          document.querySelector('[name="subject"]').value          = draft.subject;
        if (draft.terms)            document.querySelector('[name="terms"]').value            = draft.terms;
        if (draft.guarantee)        document.querySelector('[name="guarantee"]').value        = draft.guarantee;
        if (draft.payment)          document.querySelector('[name="payment"]').value          = draft.payment;
        (draft.groups || []).forEach(g => {
            if (hasValidDraftGroup(g)) {
                addSlGroup(g.services || [], g, true);
            }
        });
        recalc();
    } catch(e) { localStorage.removeItem(LS_KEY); }
}

document.getElementById('invoiceForm').addEventListener('submit', () => {
    localStorage.removeItem(LS_KEY);
});

document.getElementById('invoiceForm').addEventListener('input', scheduleSave);

function fmtCurr(n) {
    return currSym + (parseFloat(n)||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
function esc(t) {
    if (!t) return '';
    const d = document.createElement('div');
    d.textContent = String(t); return d.innerHTML;
}
function escAttr(t) {
    return String(t||'').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function openQuickCustomer() {
    Swal.fire({
        customClass:{ popup:'quick-customer-popup' },
        width:520,
        html:`<div class="quick-customer">
                <div class="quick-customer-head">
                    <div class="quick-customer-icon"><i class="bi bi-person-plus"></i></div>
                    <div>
                        <h2 class="quick-customer-title">Quick Add Customer</h2>
                        <div class="quick-customer-sub">Create a customer and select it for this invoice.</div>
                    </div>
                </div>
                <div class="quick-customer-body">
                    <div class="quick-customer-grid">
                        <div class="quick-field full">
                            <label for="qcName">Customer name <span class="text-danger">*</span></label>
                            <div class="quick-input-wrap">
                                <i class="bi bi-person"></i>
                                <input id="qcName" class="quick-input" placeholder="Enter customer name" autocomplete="name">
                            </div>
                        </div>
                        <div class="quick-field">
                            <label for="qcMobile">Mobile</label>
                            <div class="quick-input-wrap">
                                <i class="bi bi-phone"></i>
                                <input id="qcMobile" class="quick-input" placeholder="Phone number" inputmode="tel" autocomplete="tel">
                            </div>
                        </div>
                        <div class="quick-field">
                            <label for="qcEmail">Email</label>
                            <div class="quick-input-wrap">
                                <i class="bi bi-envelope"></i>
                                <input id="qcEmail" class="quick-input" placeholder="customer@example.com" type="email" autocomplete="email">
                            </div>
                        </div>
                    </div>
                    <div class="quick-customer-note">
                        <i class="bi bi-check-circle"></i>
                        <span>The new customer will be added to the list and selected automatically.</span>
                    </div>
                </div>
              </div>`,
        showCancelButton:true,
        confirmButtonText:'<i class="bi bi-plus-lg me-1"></i>Add Customer',
        cancelButtonText:'Cancel',
        buttonsStyling:true,
        confirmButtonColor:'var(--primary,#4f46e5)',
        focusConfirm:false,
        didOpen:() => document.getElementById('qcName')?.focus(),
        preConfirm:() => {
            const name = document.getElementById('qcName').value.trim();
            if (!name) { Swal.showValidationMessage('Name is required'); return false; }
            const email = document.getElementById('qcEmail').value.trim();
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                Swal.showValidationMessage('Enter a valid email address');
                return false;
            }
            const mobile = document.getElementById('qcMobile').value.trim();
            return {
                name,
                mobile: mobile || null,
                email: email || null
            };
        }
    }).then(r => {
        if (!r.isConfirmed || !r.value) return;
        $.post('{{ route("admin.customers.quick-store") }}', r.value, res => {
            const sel = document.querySelector('[name="customer_id"]');
            sel.appendChild(new Option(
                res.customer.name + (res.customer.company_name?' ('+res.customer.company_name+')':''),
                res.customer.id, true, true
            ));
            Swal.fire({icon:'success',text:'Customer added.',timer:1500,showConfirmButton:false});
        }).fail(x => {
            let msg = 'Failed to create customer.';
            if (x.responseJSON) {
                if (x.responseJSON.errors) {
                    msg = Object.values(x.responseJSON.errors).flat().join('<br>');
                } else if (x.responseJSON.message) {
                    msg = x.responseJSON.message;
                }
            } else if (x.status === 419) {
                msg = 'Session expired. Please refresh the page.';
            } else if (x.status === 403) {
                msg = 'You do not have permission to add customers.';
            }
            Swal.fire({ icon: 'error', title: 'Error', html: msg });
        });
    });
}

// Load existing groups on EDIT
@if($isEdit && $invoice->items->count())
(function(){
    const raw     = @json($invoice->items->sortBy('sl_no')->sortBy('sort_order')->values());
    const grouped = {};
    raw.forEach(item => {
        const sl = item.sl_no || 1;
        if (!grouped[sl]) {
            const formMode = item.area_mode === 'sqft' ? 'sqft'
                           : item.area_mode === 'nos'  ? 'nos'
                           : 'fixed';
            grouped[sl] = {
                mode:         formMode,
                price:        item.price        || 0,
                area_value:   item.area_value   || '',
                rate_per_sft: item.rate_per_sft || '',
                area_nos:     item.area_nos     || '',
                rate_per_nos: item.rate_per_nos || '',
                services:     [],
                total:        0,
            };
        }
        grouped[sl].total += parseFloat(item.total || 0);
        grouped[sl].services.push({
            id:          item.service_id,
            category_id: item.category_id,
            title:       item.title,
            description: item.description || '',
            cat_name:    '',
        });
    });
    Object.values(grouped).forEach(g => {
        g.price = g.total > 0 ? g.total : g.price;
        addSlGroup(g.services, g, true);
    });
})();
@elseif(!$isEdit && $selectedQuotation && $selectedQuotation->items->count())
(function(){
    localStorage.removeItem(LS_KEY); // Clean draft when opening a specific quotation
    const raw     = @json($selectedQuotation->items->sortBy('sl_no')->sortBy('sort_order')->values());
    const grouped = {};
    raw.forEach(item => {
        const sl = item.sl_no || 1;
        if (!grouped[sl]) {
            const formMode = item.area_mode === 'sqft' ? 'sqft'
                           : item.area_mode === 'nos'  ? 'nos'
                           : 'fixed';
            grouped[sl] = {
                mode:         formMode,
                price:        item.price        || 0,
                area_value:   item.area_value   || '',
                rate_per_sft: item.rate_per_sft || '',
                area_nos:     item.area_nos     || '',
                rate_per_nos: item.rate_per_nos || '',
                services:     [],
                total:        0,
            };
        }
        grouped[sl].total += parseFloat(item.total || 0);
        grouped[sl].services.push({
            id:          item.service_id,
            category_id: item.category_id,
            title:       item.title,
            description: item.description || '',
            cat_name:    '',
        });
    });
    Object.values(grouped).forEach(g => {
        g.price = g.total > 0 ? g.total : g.price;
        addSlGroup(g.services, g, true);
    });
})();
@else
restoreDraft();
@endif

recalc();
</script>
@endpush