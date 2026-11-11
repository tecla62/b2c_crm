@php
    // ── Theme & Currency ──────────────────────────────────────────────────
    $primaryColor     = setting('theme_color', '#4f46e5');
    $pdfHeaderColor   = setting('pdf_header_color', $primaryColor);
    $currSym          = setting('currency_symbol', '₹');

    // ── Company Identity ──────────────────────────────────────────────────
    $companyName    = setting('company_name',    config('app.name'));
    $companyTagline = setting('company_tagline', '');
    $companyPhone   = setting('company_mobile',  '');
    $companyAltPhone= setting('company_alt_mobile', '');
    $companyEmail   = setting('company_email',   '');
    $companyWebsite = setting('company_website', '');
    $companyOwner   = setting('company_owner_name', '');
    $companyGst     = setting('company_gst',     '');
    $companyPan     = setting('company_pan',     '');
    $companyCin     = setting('company_cin',     '');
    $companyMsme    = setting('company_msme',    '');

    // ── Address ───────────────────────────────────────────────────────────
    $addrStreet  = setting('company_address', '');
    $addrCity    = setting('company_city',    '');
    $addrState   = setting('company_state',   '');
    $addrPincode = setting('company_pincode', '');
    $addrCountry = setting('company_country', '');
    $companyAddress = implode(', ', array_filter([$addrStreet, $addrCity, $addrState, $addrPincode]));

    // ── Bank Details ──────────────────────────────────────────────────────
    $bankName    = setting('bank_name',           '');
    $bankAcName  = setting('bank_account_name',   '');
    $bankAcNo    = setting('bank_account_number', '');
    $bankIfsc    = setting('bank_ifsc',           '');
    $bankBranch  = setting('bank_branch',         '');
    $bankUpi     = setting('bank_upi_id',         '');
    // Helper to get base64 image to fix DOMPDF image loading issues
    if (!function_exists('get_base64_img')) {
        function get_base64_img($key) {
            $val = \App\Models\Setting::get($key);
            if ($val) {
                $path = public_path('storage/' . $val);
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    return 'data:image/' . ($type === 'jpg' ? 'jpeg' : $type) . ';base64,' . base64_encode($data);
                }
            }
            return '';
        }
    }

    $bankQrUrl          = get_base64_img('bank_qr_code');
    $logoUrl            = get_base64_img('company_logo');
    $estimationLogoUrl  = get_base64_img('pdf_estimation_logo');
    $headerLogoUrl      = $estimationLogoUrl ?: $logoUrl;

    $watermarkUrl     = get_base64_img('pdf_watermark_image');
    $watermarkOpacity = setting('pdf_watermark_opacity', '15');
    $signatureUrl     = get_base64_img('pdf_signature');
    $sealUrl          = get_base64_img('pdf_company_seal');
    $pdfFooterText    = setting('pdf_footer_text', 'This is a computer generated document.');

    // ── Default Terms & Conditions from Settings ─────────────────────────────
    $defaultTerms     = setting('quotation_terms',     '');
    $defaultGuarantee = setting('quotation_guarantee', '');
    $defaultPayment   = setting('quotation_payment',   '');

    $termsText     = !empty($quotation->terms)     ? $quotation->terms     : $defaultTerms;
    $guaranteeText = !empty($quotation->guarantee) ? $quotation->guarantee : $defaultGuarantee;
    $paymentText   = !empty($quotation->payment)   ? $quotation->payment   : $defaultPayment;

    $hasTerms     = !empty($termsText);
    $hasGuarantee = !empty($guaranteeText);
    $hasPayment   = !empty($paymentText);

    // ── Group items by sl_no (common pricing / SL group = same serial) ───
    $showDesc   = isset($showDesc) ? $showDesc : request()->query('desc', '1') !== '0';
    $colCount   = $showDesc ? 5 : 4;
    $sumColspan = $colCount - 1;
    $groups = $quotation->items
        ->sortBy(['sl_no', 'sort_order'])
        ->groupBy(fn ($item) => (int) $item->sl_no)
        ->sortKeys();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=1024">
<title>{{ $quotation->quotation_number }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:13px;color:#333;background:#eceff1;line-height:1.5;}

/* ── Toolbar ── */
.toolbar{
    position:sticky;top:0;z-index:100;
    background:#1e293b;padding:10px 20px;
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.toolbar-title{color:#94a3b8;font-size:12px;font-weight:600;margin-right:auto;}
.btn-tool{
    padding:7px 16px;border:none;border-radius:7px;
    font-size:12px;font-weight:600;cursor:pointer;
    display:inline-flex;align-items:center;gap:6px;
    transition:opacity .15s;
}
.btn-tool:hover{opacity:.85;}
.btn-print{background:{{ $primaryColor }};color:#fff;}
.btn-back{background:#334155;color:#cbd5e1;}
.btn-toggle{background:#0f172a;color:#94a3b8;border:1px solid #334155;}
.btn-toggle.active{background:{{ $pdfHeaderColor }};color:#fff;border-color:{{ $pdfHeaderColor }};}
.divider{width:1px;height:24px;background:#334155;}

/* ── Page ── */
.page{
    width:800px; margin:24px auto;
    background:#fff;
    box-shadow:0 1px 12px rgba(0,0,0,.06);
    padding:40px 44px 32px;
    position:relative;
    overflow:hidden;
    border:1px solid #d1d5db;
    border-radius:14px;
}

/* ── Watermark ── */
.watermark{
    position:absolute;
    top:50%;
    left:50%;
    width:100%;
    transform:translate(-50%,-50%) rotate(-30deg);
    display:block;
    text-align:center;
    pointer-events:none;
    user-select:none;
    z-index:0;
}
.watermark img{display:inline-block;max-width:420px;height:auto;object-fit:contain;opacity:.9;}
.watermark-text{
    font-size:64px;font-weight:900;color:{{ $pdfHeaderColor }};
    white-space:nowrap;letter-spacing:3px;opacity:.06;
}
.page > *:not(.watermark){position:relative;z-index:1;}

@if(request()->routeIs('*.pdf'))
body { background: #fff; }
.page { 
    max-width: 100%; margin: 0; padding: 0; 
    box-shadow: none; border: none; border-radius: 0; 
    overflow: visible;
}
@endif

/* ══ HEADER ══ */
.doc-header{
    display:table;width:100%;
    margin-bottom:36px;
    border-bottom:2px solid {{ $pdfHeaderColor }};
    padding-bottom:20px;
}
.doc-header-left{
    display:table-cell;vertical-align:middle; width:50%;
}
.doc-header-left .company-logo img{
    height:74px;max-width:260px;object-fit:contain;display:block;
}
.doc-header-left .co-name-text{
    font-size:26px;font-weight:800;color:{{ $pdfHeaderColor }};letter-spacing:-.5px;
}
.doc-header-right{
    display:table-cell;vertical-align:bottom;text-align:right; width:50%; max-width:50%; word-wrap:break-word;
}
.est-number{
    font-size:22px;font-weight:800;color:#111;letter-spacing:.5px;
    text-transform:uppercase;margin-bottom:4px;
}
.est-meta{font-size:12px;color:#666;line-height:1.6;}
.est-meta strong{color:#333;font-weight:700;}
.est-status{
    display:inline-block;margin-top:10px;padding:4px 12px;
    border-radius:4px;font-size:10px;font-weight:700;
    text-transform:uppercase;letter-spacing:1px;
    background:{{ $pdfHeaderColor }};color:#fff;
}
.est-subject{font-size:12px;color:#555;margin-top:8px;font-style:italic;}

/* ══ Party Information ══ */
.party-row{
    display:table;width:100%;
    margin-bottom:32px;
}
.party-col{
    display:table-cell;width:50%;vertical-align:top;
    font-size:13px;line-height:1.6;color:#444;
}
.party-col-right{
    text-align:right;
}
.from-name{font-size:15px;font-weight:700;color:#111;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;}
.from-line{color:#555;}
.from-gst{margin-top:6px;color:#555;font-size:12px;}
.to-label{
    font-size:11px;font-weight:700;color:#888;
    margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;
}
.to-name{font-size:15px;font-weight:700;color:#111;margin-bottom:4px;}
.to-line{color:#444;}
.party-badge{
    display:inline-block;margin-top:8px;
    font-size:11px;font-weight:700;color:{{ $pdfHeaderColor }};
}

/* ══ ITEMS TABLE ══ */
.sl-table{width:100%;border-collapse:collapse;margin-bottom:0;border:1px solid #ddd;}
.sl-table thead tr{background:#f5f5f5;}
.sl-table thead th{
    padding:10px 12px;color:#222;font-size:11px;
    font-weight:700;text-align:left;
    border:1px solid #ddd;
}
.sl-table thead th.center{text-align:center;}
.sl-table thead th.right{text-align:right;}
.sl-table tbody td{
    padding:10px 12px;vertical-align:top;
    border:1px solid #ddd;font-size:12px;color:#333;
}
.sl-table tbody td.center{text-align:center;}
.sl-table tbody td.right{text-align:right;}
.sl-num{font-weight:700;color:#222;font-size:12px;}
.mode-pill{
    display:inline-block;
    background:#f8f8f8;color:{{ $pdfHeaderColor }};
    border-radius:3px;padding:2px 8px;
    font-size:9px;font-weight:700;margin-bottom:5px;
    border:1px solid #e5e5e5;
}
.group-table{width:100%;border-collapse:collapse;margin:0;border-spacing:0;}
.group-table td{padding:8px 0;vertical-align:top;}
.group-table tr + tr td{border-top:1px dashed #eee;}
.svc-item{padding:0;margin:0;}
.svc-title{font-weight:700;color:#222;font-size:12.5px; margin-bottom:8px;}
.svc-desc{font-size:11px;color:#666;margin-top:3px;line-height:1.55;}
.cat-badge{
    display:inline-block;background:#f5f5f5;color:{{ $pdfHeaderColor }};
    border-radius:3px;padding:1px 6px;font-size:9px;font-weight:600;
    margin-top:3px;border:1px solid #e8e8e8;
}
.group-amount{font-size:13px;font-weight:700;color:#222;white-space:nowrap;}

/* ══ TOTALS (table footer, reference style) ══ */
.sl-table tfoot td,
    .summary-table td{
        padding:9px 12px;border:1px solid #ddd;font-size:12.5px;
    }
    .sl-table tfoot .sum-label,
    .summary-table .sum-label{
        text-align:right;font-weight:700;color:#222;
        background:#fff;
    }
    .sl-table tfoot .sum-val,
    .summary-table .sum-val{
        text-align:right;font-weight:700;color:#222;
        background:#fff;white-space:nowrap;
    }
    .sl-table tfoot .sum-val.accent,
    .summary-table .sum-val.accent{color:{{ $pdfHeaderColor }};font-size:14px;}
    .sl-table tfoot tr.grand-row .sum-label,
    .summary-table tr.grand-row .sum-label,
    .sl-table tfoot tr.grand-row .sum-val,
    .summary-table tr.grand-row .sum-val{
        font-size:14px;font-weight:800;
        border-top:2px solid #ddd;
    }
    .sl-table tfoot tr.grand-row .sum-val.accent,
    .summary-table tr.grand-row .sum-val.accent{font-size:15px;}
    .summary-table{
        width:100%;border-collapse:collapse;margin-top:10px;
    }
    .summary-table td{background:#fff;}

/* ══ TERMS — simple one-by-one list ══ */
.terms-section{margin:28px 0 24px;}
.terms-section-title{
    font-size:12px;font-weight:900;letter-spacing:.9px;text-transform:uppercase;
    color:{{ $pdfHeaderColor }};margin-bottom:18px;padding-bottom:8px;
    border-bottom:2px solid {{ $pdfHeaderColor }};
}
.terms-block{margin-bottom:18px;padding:18px;border:1px solid #e2e8f0;border-radius:12px;background:#fafbff;page-break-inside:avoid;}
.terms-block-header{font-size:13px;font-weight:700;color:#111;margin-bottom:10px;}
.terms-block-header .dot{width:10px;height:10px;border-radius:5px;display:inline-block;margin-right:8px;}
.terms-block-body{font-size:12px;color:#444;line-height:1.75;white-space:pre-line;}
.sl-table.hide-desc .desc-col{display:none!important;}

/* ══ BANK ══ */
.bank-wrap{display:table;width:100%;margin-bottom:32px;border-spacing:16px 0;}
.bank-box{display:table-cell;vertical-align:top;border:1px solid #e2e8f0;background:#f8fafc;page-break-inside:avoid;}
.bank-box-header{
    padding:10px 14px;font-size:11px;font-weight:700;letter-spacing:.5px;
    text-transform:uppercase;background:{{ $pdfHeaderColor }};color:#fff;
}
.bank-box-body{padding:14px;font-size:12px;color:#444;line-height:1.8;}

/* ══ SIGN ══ */
.sign-row{
    display:table;width:100%;margin:40px 0 20px;
    padding-top:30px;border-top:1px solid #e2e8f0;page-break-inside:avoid;
}
.sign-box{display:table-cell;width:50%;text-align:center;vertical-align:bottom;}
.sign-seal-img{height:80px;max-width:180px;object-fit:contain;margin:0 auto 10px;display:block;}
.sign-line{border-bottom:1px solid #111;height:40px;width:180px;margin:0 auto 10px;}
.sign-label{font-size:11px;color:#666;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}

/* ══ AUTHENTICITY ══ */
.auth-box{
    margin-bottom:24px;padding:12px 16px;
    background:#fafafa;border-left:3px solid {{ $pdfHeaderColor }};page-break-inside:avoid;
}
.auth-title{
    font-size:10px;font-weight:700;color:{{ $pdfHeaderColor }};
    margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;
}
.auth-body{font-size:11px;color:#555;line-height:1.7;}

/* ── Footer ── */
.print-footer{
    text-align:center;padding-top:16px;margin-top:8px;
}
.print-footer .stmt{
    font-size:11px;color:#aaa;font-style:italic;margin-bottom:8px;
}
.print-footer .meta{font-size:10px;color:#bbb;}

/* ── Print ── */
@media print{
    body{background:#fff;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
    .toolbar{display:none!important;}
    .page{margin:0;box-shadow:none;padding:16px 20px;overflow:visible;}
    .watermark{
        position:fixed;
        top:50%;
        left:50%;
        width:100%;
        transform:translate(-50%,-50%) rotate(1deg);
        z-index:0;
        pointer-events:none;
        user-select:none;
    }
    .watermark img{max-width:420px;height:auto;opacity:.9;}
    .sl-table tbody tr{page-break-inside:avoid;}
    .terms-block{page-break-inside:avoid;}
}


</style>
</head>
<body>

{{-- ── Toolbar ── --}}
@if(!request()->routeIs('*.pdf'))
<div class="toolbar no-print">
    <span class="toolbar-title">{{ $quotation->quotation_number }} &nbsp;·&nbsp; Print Preview</span>

    <button class="btn-tool btn-toggle {{ $showDesc ? 'active' : '' }}" id="btnWithDesc" onclick="toggleDesc(true)">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
        With Description
    </button>
    <button class="btn-tool btn-toggle {{ $showDesc ? '' : 'active' }}" id="btnNoDesc" onclick="toggleDesc(false)">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h10"/></svg>
        Without Description
    </button>

    <div class="divider"></div>

    <button class="btn-tool btn-print" onclick="window.print()">
        Print / Save PDF
    </button>
    <a class="btn-tool btn-back" href="{{ route('admin.quotations.show', $quotation) }}">
        Back
    </a>
</div>
@endif

<div class="page">

    {{-- Watermark --}}
    <div class="watermark" style="opacity:{{ $watermarkUrl ? ($watermarkOpacity/100) : '0.06' }};">
        @if($watermarkUrl)
            <img src="{{ $watermarkUrl }}" alt="Watermark">
        @elseif($estimationLogoUrl)
            <img src="{{ $estimationLogoUrl }}" alt="">
        @elseif($logoUrl)
            <img src="{{ $logoUrl }}" alt="">
        @else
            <div class="watermark-text">{{ $companyName }}</div>
        @endif
    </div>

    {{-- ══ HEADER: Estimation logo | Estimate meta ══ --}}
    <div class="doc-header">
        <div class="doc-header-left">
            @if($headerLogoUrl)
                <div class="company-logo"><img src="{{ $headerLogoUrl }}" alt="{{ $companyName }}"></div>
            @else
                <div class="co-name-text">{{ $companyName }}</div>
            @endif
        </div>
        <div class="doc-header-right">
            <div class="est-number">{{ strtoupper($quotation->quotation_number) }}</div>
            <div class="est-meta"><strong>Create Date:</strong> {{ $quotation->date->format('d M Y') }}</div>
            @if($quotation->valid_until)
            <div class="est-meta"><strong>Expiry Date:</strong> {{ $quotation->valid_until->format('d M Y') }}</div>
            @endif
            @if($quotation->subject)
            <div class="est-subject">{{ $quotation->subject }}</div>
            @endif
        </div>
    </div>

    {{-- ══ Company (left) | Client (right) ══ --}}
    <div class="party-row">
        <div class="party-col">
            <div class="from-name">{{ $companyName }}</div>
             @if($companyOwner)<div class="from-line"><strong>Owner:</strong> {{ $companyOwner }}</div>@endif
            @if($companyTagline)<div class="from-line">{{ $companyTagline }}</div>@endif
            @if($companyAddress)<div class="from-line">{{ $companyAddress }}</div>@endif
            @if($companyPhone || $companyEmail)
            <div class="from-line">
                @if($companyPhone){{ $companyPhone }}@endif
                @if($companyPhone && $companyEmail) &nbsp;·&nbsp; @endif
                @if($companyEmail){{ $companyEmail }}@endif
                @if($companyAltPhone) &nbsp;·&nbsp; {{ $companyAltPhone }}@endif
            </div>
            @endif
           
            @if($companyGst || $companyPan || $companyCin)
            <div class="from-gst">
                @if($companyGst)<strong>GST No:</strong> {{ $companyGst }}@endif
                @if($companyGst && ($companyPan || $companyCin)) &nbsp;·&nbsp; @endif
                @if($companyPan)<strong>PAN:</strong> {{ $companyPan }}@endif
                @if($companyPan && $companyCin) &nbsp;·&nbsp; @endif
            
            </div>
            @endif
        </div>
        <div class="party-col party-col-right">
            <div class="to-label">Estimate to:</div>
            <div class="to-name">{{ $quotation->customer->name }}</div>
            @if($quotation->customer->company_name)<div class="to-line">{{ $quotation->customer->company_name }}</div>@endif
            @if(!empty($quotation->customer->address))<div class="to-line">{{ $quotation->customer->address }}</div>@endif
            @if($quotation->customer->mobile)<div class="to-line"><strong>{{ $quotation->customer->mobile }}</strong></div>@endif
            @if($quotation->customer->email)<div class="to-line"><strong>{{ $quotation->customer->email }}</strong></div>@endif
            @if(!empty($quotation->customer->gst_number))
            <div class="party-badge">GST: {{ $quotation->customer->gst_number }}</div>
            @endif
        </div>
    </div>

    {{-- ══ ITEMS TABLE ══ --}}
    {{--
        Column visibility:
        - #col-desc thead th and tbody td are toggled by JS based on "with/without description"
        - When "without description" is selected, the description column header and all desc cells are hidden
        - Each sl_no group is ONE row; items with same sl_no share that row
        - Mode info (sqft/nos/fixed) shown as a pill in the item cell, not a separate column
    --}}
        <table class="sl-table {{ $showDesc ? '' : 'hide-desc' }}" id="itemsTable" data-sum-colspan="{{ $sumColspan }}">
        <thead>
            <tr>
                <th class="center" style="width:42px;">S.No</th>
                <th>Item</th>
                <th class="desc-col">Description</th>
                <th style="width:140px;">Area / Mode</th>
                <th class="right" style="width:110px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $slNo => $items)
            @php
                $first    = $items->first();
                $mode     = $first->area_mode ?? 'manual';
                $groupAmt = $items->sum('total');
            @endphp
            <tr>
                <td class="center" style="vertical-align:top;padding-top:12px;">
                    <span class="sl-num">{{ $loop->iteration }}</span>
                </td>

                {{-- All services in same SL group share one serial number --}}
                <td class="item-block-cell" style="vertical-align:top;padding:0;" colspan="2">
                    <table class="group-table">
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td style="width:35%;padding-right:12px;">
                                    <div class="svc-item">
                                        <div class="svc-title">{{ $item->title }}</div>
                                    </div>
                                </td>
                                <td class="desc-col">
                                    @if($item->description)
                                        <div class="svc-desc desc-field">{{ $item->description }}</div>
                                    @elseif($item->service && $item->service->description)
                                        <div class="svc-desc" style="opacity:.55;font-style:italic;">
                                            <i class="bi bi-info-circle me-1" style="font-size:10px;"></i>{{ $item->service->description }}
                                            <span class="badge bg-light text-muted ms-1" style="font-size:9px;font-style:normal;">from service</span>
                                        </div>
                                    @else
                                        <div style="color:#cbd5e1;font-size:11px;">—</div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>

                {{-- Area / Mode --}}
                <td style="vertical-align:top;color:#64748b;font-size:12px;">
                    @if($mode === 'sqft')
                        <div class="mode-pill">SqFt × Rate</div>
                        @if($first->area_value)
                        <div><strong>{{ number_format($first->area_value, 2) }}</strong> sqft</div>
                        @if($first->rate_per_sft)
                        <div style="color:#94a3b8;font-size:11px;">@ {{ $currSym }}{{ number_format($first->rate_per_sft, 2) }}/sqft</div>
                        @endif
                        @endif
                    @elseif($mode === 'nos')
                        <div class="mode-pill">Nos × Rate</div>
                        @if($first->area_nos)
                        <div><strong>{{ number_format($first->area_nos, 0) }}</strong> nos</div>
                        @if($first->rate_per_nos)
                        <div style="color:#94a3b8;font-size:11px;">@ {{ $currSym }}{{ number_format($first->rate_per_nos, 2) }}/unit</div>
                        @endif
                        @endif
                    @else
                        <div class="mode-pill">Fixed</div>
                    @endif
                </td>

                {{-- Amount --}}
                <td class="right" style="vertical-align:top;padding-top:14px;">
                    <span class="group-amount">{{ $currSym }}{{ number_format($groupAmt, 2) }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#94a3b8;padding:28px;">No items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        <tbody>
            <tr>
                <td colspan="{{ $sumColspan }}" class="sum-label sum-colspan">Subtotal</td>
                <td class="sum-val">{{ $currSym }}{{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $sumColspan }}" class="sum-label sum-colspan">Tax ({{ $quotation->tax_percent ?? 0 }}%)</td>
                <td class="sum-val">{{ $currSym }}{{ number_format($quotation->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $sumColspan }}" class="sum-label sum-colspan">
                    Discount{{ $quotation->discount_percent > 0 ? ' (' . $quotation->discount_percent . '%)' : '' }}
                </td>
                <td class="sum-val accent">- {{ $currSym }}{{ number_format($quotation->discount, 2) }}</td>
            </tr>
            <tr class="grand-row">
                <td colspan="{{ $sumColspan }}" class="sum-label sum-colspan">Grand Total</td>
                <td class="sum-val accent">{{ $currSym }}{{ number_format($quotation->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ══ TERMS / GUARANTEE / PAYMENT — stacked list ══ --}}
    @if($hasTerms || $hasGuarantee || $hasPayment)
    <div class="terms-section">
        <div class="terms-section-title">Terms, Conditions &amp; Guarantee</div>

        @if($hasTerms)
        <div class="terms-block">
            <div class="terms-block-header">
                <span class="dot" style="background:{{ $pdfHeaderColor }};"></span>
                Terms &amp; Conditions
            </div>
            <div class="terms-block-body">{{ $termsText }}</div>
        </div>
        @endif

        @if($hasGuarantee)
        <div class="terms-block">
            <div class="terms-block-header">
                <span class="dot" style="background:#16a34a;"></span>
                Guarantee &amp; Warranty
            </div>
            <div class="terms-block-body">{{ $guaranteeText }}</div>
        </div>
        @endif

        @if($hasPayment)
        <div class="terms-block">
            <div class="terms-block-header">
                <span class="dot" style="background:#d97706;"></span>
                Payment Terms
            </div>
            <div class="terms-block-body">{{ $paymentText }}</div>
        </div>
        @endif
    </div>
    @endif

    {{-- ══ BANK DETAILS ══ --}}
    @if($bankName || $bankAcNo || $bankUpi)
    <div class="bank-wrap">
        @if($bankName || $bankAcNo)
        <div class="bank-box">
            <div class="bank-box-header">
                Bank Details
            </div>
            <div class="bank-box-body">
                @if($bankName)
                <div>
                    <span style="color:#94a3b8;font-size:11px;">Bank:</span>
                    <strong>{{ $bankName }}</strong>
                    @if($bankBranch) &nbsp;·&nbsp; {{ $bankBranch }} @endif
                </div>
                @endif
                @if($bankAcName)<div><span style="color:#94a3b8;font-size:11px;">A/C Name:</span> <strong>{{ $bankAcName }}</strong></div>@endif
                @if($bankAcNo)<div><span style="color:#94a3b8;font-size:11px;">A/C No:</span> <strong style="font-family:monospace;letter-spacing:1px;">{{ $bankAcNo }}</strong></div>@endif
                @if($bankIfsc)<div><span style="color:#94a3b8;font-size:11px;">IFSC:</span> <strong style="font-family:monospace;">{{ $bankIfsc }}</strong></div>@endif
            </div>
        </div>
        @endif
        @if($bankUpi || $bankQrUrl)
        <div class="bank-box" style="min-width:150px;text-align:center;">
            <div class="bank-box-header" style="text-align:center;">UPI / QR Pay</div>
            <div style="padding:12px;">
                @if($bankQrUrl)<img src="{{ $bankQrUrl }}" style="width:85px;height:85px;object-fit:contain;display:block;margin:0 auto 6px;">@endif
                @if($bankUpi)<div style="font-size:11.5px;font-weight:700;color:{{ $pdfHeaderColor }};word-break:break-all;">{{ $bankUpi }}</div>@endif
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ══ SIGNATURE & SEAL ══ --}}
    <div class="sign-row">
        <div class="sign-box">
            @if($signatureUrl)
                <img src="{{ $signatureUrl }}" alt="Authorised Signature" class="sign-seal-img">
            @else
                <div class="sign-line"></div>
            @endif
            <div class="sign-label">Authorised By</div>
        </div>
        <div class="sign-box">
            @if($sealUrl)
                <img src="{{ $sealUrl }}" alt="Company Seal" class="sign-seal-img">
            @else
                <div class="sign-line"></div>
            @endif
            <div class="sign-label">Company Seal</div>
        </div>
    </div>

    {{-- ══ AUTHENTICITY STATEMENT ══ --}}
    <div class="auth-box">
        <div class="auth-title">Document Authenticity</div>
        <div class="auth-body">
            @php
                if ($quotation->valid_until) {
                    $days = $quotation->valid_until->diffInDays(now());
                    $validText = $days . ' day' . ($days != 1 ? 's' : '');
                } else {
                    $validText = '30 days';
                }
            @endphp
            This quotation is valid for <strong>{{ $validText }}</strong> from the quotation date.
            All services and pricing mentioned herein are subject to the terms and conditions specified above.
            This document has been digitally authorized and sealed by {{ $companyName }}.
        </div>
    </div>

    {{-- ══ FOOTER ══ --}}
    <div class="print-footer">
        <div class="stmt">...{{ $pdfFooterText }}...</div>
        <div class="meta">
            <strong>{{ $companyName }}</strong>
            @if($companyEmail) &nbsp;·&nbsp; {{ $companyEmail }} @endif
            @if($companyPhone) &nbsp;·&nbsp; {{ $companyPhone }} @endif
            &nbsp;·&nbsp; Generated {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>

</div>

<script>
const showDesc = new URLSearchParams(window.location.search).get('desc') !== '0';
const autoPrint = new URLSearchParams(window.location.search).get('autoprint') === '1';

function toggleDesc(show) {
    document.querySelectorAll('.desc-col').forEach(el => {
        el.style.display = show ? '' : 'none';
    });
    document.querySelectorAll('.sum-colspan').forEach(el => {
        el.colSpan = show ? 4 : 3;
    });
    document.getElementById('btnWithDesc').classList.toggle('active', show);
    document.getElementById('btnNoDesc').classList.toggle('active', !show);
}

toggleDesc(showDesc);

if (autoPrint) {
    window.addEventListener('load', () => setTimeout(() => window.print(), 800));
}
</script>
</body>
</html>