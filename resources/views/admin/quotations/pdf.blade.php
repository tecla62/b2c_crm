@php
    $primaryColor     = setting('theme_color', '#4f46e5');
    $pdfHeaderColor   = setting('pdf_header_color', $primaryColor);
    $currSym          = '₹';

    $companyName     = setting('company_name',    config('app.name'));
    $companyTagline  = setting('company_tagline', '');
    $companyPhone    = setting('company_mobile',  '');
    $companyAltPhone = setting('company_alt_mobile', '');
    $companyEmail    = setting('company_email',   '');
    $companyWebsite  = setting('company_website', '');
    $companyOwner    = setting('company_owner_name', '');
    $companyGst      = setting('company_gst',     '');
    $companyPan      = setting('company_pan',     '');
    $companyCin      = setting('company_cin',     '');

    $addrStreet  = setting('company_address', '');
    $addrCity    = setting('company_city',    '');
    $addrState   = setting('company_state',   '');
    $addrPincode = setting('company_pincode', '');
    $companyAddress = implode(', ', array_filter([$addrStreet, $addrCity, $addrState, $addrPincode]));

    $bankName   = setting('bank_name',           '');
    $bankAcName = setting('bank_account_name',   '');
    $bankAcNo   = setting('bank_account_number', '');
    $bankIfsc   = setting('bank_ifsc',           '');
    $bankBranch = setting('bank_branch',         '');
    $bankUpi    = setting('bank_upi_id',         '');

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

    $bankQrUrl         = get_base64_img('bank_qr_code');
    $logoUrl           = get_base64_img('company_logo');
    $estimationLogoUrl = get_base64_img('pdf_estimation_logo');
    $headerLogoUrl     = $estimationLogoUrl ?: $logoUrl;
    $watermarkUrl      = get_base64_img('pdf_watermark_image');
    $signatureUrl      = get_base64_img('pdf_signature');
    $sealUrl           = get_base64_img('pdf_company_seal');
    $pdfFooterText     = setting('pdf_footer_text', 'This is a computer generated document.');

    $defaultTerms     = setting('quotation_terms',     '');
    $defaultGuarantee = setting('quotation_guarantee', '');
    $defaultPayment   = setting('quotation_payment',   '');
    $termsText     = !empty($quotation->terms)     ? $quotation->terms     : $defaultTerms;
    $guaranteeText = !empty($quotation->guarantee) ? $quotation->guarantee : $defaultGuarantee;
    $paymentText   = !empty($quotation->payment)   ? $quotation->payment   : $defaultPayment;
    $hasTerms     = !empty($termsText);
    $hasGuarantee = !empty($guaranteeText);
    $hasPayment   = !empty($paymentText);

    $showDesc   = isset($showDesc) ? $showDesc : true;
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $quotation->quotation_number }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

@page {
    size: A4 portrait;
    margin: 12mm 12mm 18mm 12mm;
}

html, body {
    margin: 0;
    padding: 35px;
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 10px;
    color: #333;
    background: #fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    line-height: 1.45;
}

/* ── Frame ── */
.page-border {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    border: 3px solid #111;
    z-index: -1000;
}

/* ── Watermark ── */
.watermark {
    position: fixed;
    top: 25%;
    left: 5%;
    width: 90%;
    text-align: center;
    opacity: 0.07;
    z-index: -100;
}
.watermark img { max-width: 420px; height: auto; }
.watermark-text { font-size: 60px; font-weight: 900; color: {{ $pdfHeaderColor }}; letter-spacing: 4px; }

/* ── All layout uses real <table> tags ── */
table.layout {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
table.layout td { padding: 0; vertical-align: top; }

/* ══ HEADER ══ */
.hdr-wrap {
    width: 100%;
    border-bottom: 2.5px solid {{ $pdfHeaderColor }};
    padding-bottom: 10px;
    margin-bottom: 16px;
}
.hdr-logo img {
    height: 72px;
    max-width: 240px;
    object-fit: contain;
    display: block;
}
.hdr-logo-text {
    font-size: 17px;
    font-weight: 800;
    color: {{ $pdfHeaderColor }};
    word-break: break-word;
}
.hdr-meta {
    text-align: right;
    vertical-align: bottom;
}
.est-number {
    font-size: 15px;
    font-weight: 800;
    color: #111;
    letter-spacing: .4px;
    text-transform: uppercase;
    margin-bottom: 1px;
}
.est-meta-line { font-size: 10px; color: #666; line-height: 1.65; }
.est-meta-line strong { color: #333; font-weight: 700; }
.est-subject { font-size: 11px; color: #333; margin: 0; font-style: normal; line-height: 1.5; }
.est-subject-block { margin: 14px 0 18px; padding: 10px 12px; background: #f8fafc; border-left: 3px solid {{ $pdfHeaderColor }}; }
.est-status {
    display: inline-block;
    margin-top: 6px;
    padding: 3px 9px;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: {{ $pdfHeaderColor }};
    color: #fff;
}

/* ══ PARTY ══ */
.party-wrap { margin-bottom: 18px; }
.party-from { padding-right: 10px; }
.party-to   { padding-left: 10px; text-align: right; vertical-align: top; }

.from-name { font-size: 13px; font-weight: 700; color: #111; margin-bottom: 2px; text-transform: uppercase; }
.from-line { font-size: 10px; color: #555; line-height: 1.6; word-break: break-word; }
.from-gst  { margin-top: 3px; font-size: 10px; color: #555; word-break: break-word; }
.to-label  { font-size: 9px; font-weight: 700; color: #888; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .8px; }
.to-name   { font-size: 13px; font-weight: 700; color: #111; margin-bottom: 2px; word-break: break-word; }
.to-line   { font-size: 10px; color: #444; line-height: 1.6; word-break: break-word; }
.party-badge { font-size: 10px; font-weight: 700; color: {{ $pdfHeaderColor }}; margin-top: 4px; }

/* ══ ITEMS TABLE ══ */
.sl-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-bottom: 0;
}
.sl-table thead tr { background: {{ $pdfHeaderColor }}; }
.sl-table thead th {
    padding: 9px 7px;
    color: #fff;
    font-size: 9.5px;
    font-weight: 700;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: .3px;
    border: 1px solid {{ $pdfHeaderColor }};
    white-space: normal;
    word-break: break-word;
}
.sl-table thead th.tc { text-align: center; }
.sl-table thead th.tr { text-align: right; }
.sl-table tbody td {
    padding: 9px 7px;
    vertical-align: top;
    border: 1px solid #e0e0e0;
    font-size: 10.5px;
    color: #333;
    word-break: break-word;
    overflow-wrap: break-word;
}
.sl-table tbody tr:nth-child(even) { background: #f9fafb; }
.sl-table tbody td.tc { text-align: center; }
.sl-table tbody td.tr { text-align: right; }
.sl-num      { font-weight: 700; font-size: 11px; color: #111; }
.svc-item    { padding: 4px 0; }
.svc-item:first-child { padding-top: 0; }
.svc-title   { font-weight: 700; font-size: 11px; color: #111; margin-bottom: 1px; }
.cat-badge   { font-size: 9px; color: #888; font-weight: 600; }
.mode-label  { font-size: 10px; font-weight: 700; color: #444; margin-bottom: 2px; }
.mode-detail { font-size: 10px; color: #222; }
.mode-rate   { font-size: 9.5px; color: #94a3b8; }
.amt         { font-size: 11.5px; font-weight: 700; color: #111; white-space: nowrap; }
.group-table { width: 100%; border-collapse: collapse; }
.group-table td { padding: 6px 7px; vertical-align: top; }
.group-table tr + tr td { border-top: 1px dashed #e2e8f0; }

/* ══ SUMMARY ══ */
.sum-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.sum-table td { padding: 7px 10px; border: 1px solid #e0e0e0; font-size: 11px; }
.sum-lbl { text-align: right; font-weight: 700; color: #555; background: #fff; }
.sum-val { text-align: right; font-weight: 700; color: #111; background: #fff; white-space: nowrap; width: 110px; }
.sum-val.ac { color: {{ $pdfHeaderColor }}; }
.sum-grand td { font-size: 13px; font-weight: 800; border-top: 2px solid {{ $pdfHeaderColor }}; background: #f8fafc; }

/* ══ TERMS ══ */
.terms-section { margin: 20px 0 16px; }
.terms-title {
    font-size: 11.5px; font-weight: 800; text-transform: uppercase;
    color: {{ $pdfHeaderColor }}; margin-bottom: 10px;
    padding-bottom: 4px; border-bottom: 1px solid #e2e8f0;
}
.terms-block { margin-bottom: 10px; page-break-inside: avoid; }
.terms-block-hdr { font-size: 11px; font-weight: 700; color: #111; margin-bottom: 3px; }
.terms-block-body { font-size: 10px; color: #555; line-height: 1.6; white-space: pre-line; }

/* ══ BANK ══ */
.bank-outer { margin-bottom: 20px; }
.bank-hdr {
    padding: 7px 12px;
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
    background: {{ $pdfHeaderColor }}; color: #fff;
}
.bank-body { padding: 10px 12px; font-size: 10.5px; color: #444; line-height: 1.8; border: 1px solid #e0e0e0; background: #f8fafc; }
.bank-lbl  { color: #94a3b8; font-size: 10px; }

/* ══ SIGN ══ */
.sign-outer { margin: 24px 0 14px; padding-top: 18px; border-top: 1px solid #e2e8f0; page-break-inside: avoid; }
.sign-cell  { text-align: center; vertical-align: bottom; }
.sign-seal-img { height: 84px; max-width: 180px; object-fit: contain; margin: 0 auto 7px; display: block; }
.sign-line  { border-bottom: 1px solid #333; height: 32px; width: 145px; margin: 0 auto 7px; }
.sign-lbl   { font-size: 9.5px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: .3px; }

/* ══ AUTH BOX ══ */
.auth-box {
    margin-bottom: 16px; padding: 9px 13px;
    background: #f8fafc; border-left: 4px solid {{ $pdfHeaderColor }};
    page-break-inside: avoid;
}
.auth-title { font-size: 10px; font-weight: 700; color: {{ $pdfHeaderColor }}; margin-bottom: 3px; text-transform: uppercase; }
.auth-body  { font-size: 10px; color: #555; line-height: 1.6; }

/* ══ FOOTER ══ */
footer { position: fixed; bottom: -52px; left: 0; right: 0; height: 42px; }
.foot-inner { text-align: center; border-top: 1px solid #e0e0e0; padding-top: 7px; }
.foot-stmt  { font-size: 9.5px; color: #888; font-style: italic; margin-bottom: 2px; }
.foot-meta  { font-size: 9px; color: #aaa; }

/* desc col toggle */
.hide-desc .desc-col { display: none; }
.sl-table tbody tr   { page-break-inside: avoid; }
</style>
</head>
<body>

<div class="page-border"></div>

{{-- Watermark --}}
<div class="watermark">
    @if($watermarkUrl)<img src="{{ $watermarkUrl }}" alt="">
    @elseif($estimationLogoUrl)<img src="{{ $estimationLogoUrl }}" alt="">
    @elseif($logoUrl)<img src="{{ $logoUrl }}" alt="">
    @else<div class="watermark-text">{{ $companyName }}</div>
    @endif
</div>

{{-- Fixed footer --}}
<footer>
    <div class="foot-inner">
        <div class="foot-stmt">...{{ $pdfFooterText }}...</div>
        <div class="foot-meta">
            <strong>{{ $companyName }}</strong>
            @if($companyEmail) &nbsp;&middot;&nbsp; {{ $companyEmail }}@endif
            @if($companyPhone) &nbsp;&middot;&nbsp; {{ $companyPhone }}@endif
            &nbsp;&middot;&nbsp; Generated {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>
</footer>

<div class="page">

{{-- ══════════════════════════════════════
     HEADER  — real <table> so DOMPDF
     honours exact 50/50 split
     ══════════════════════════════════════ --}}
<div class="hdr-wrap">
    <table class="layout">
        <tr>
            <td style="width:48%;vertical-align:middle;" class="hdr-logo">
                @if($headerLogoUrl)
                    <img src="{{ $headerLogoUrl }}" alt="{{ $companyName }}">
                @else
                    <div class="hdr-logo-text">{{ $companyName }}</div>
                @endif
            </td>
            <td style="width:52%;" class="hdr-meta">
                <div class="est-number">#{{ strtoupper($quotation->quotation_number) }}</div>
                <div class="est-meta-line"><strong>Create Date:</strong> {{ $quotation->date->format('d M Y') }}</div>
                @if($quotation->valid_until)
                <div class="est-meta-line"><strong>Expiry Date:</strong> {{ $quotation->valid_until->format('d M Y') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════
     PARTY ROW — real <table>
     ══════════════════════════════════════ --}}
<div class="party-wrap">
    <table class="layout">
        <tr>
            <td style="width:50%;" class="party-from">
                <div class="from-name">{{ $companyName }}</div>
                @if($companyOwner)<div class="from-line"><strong>Owner:</strong> {{ $companyOwner }}</div>@endif

                @if($companyTagline)<div class="from-line">{{ $companyTagline }}</div>@endif
                @if($companyAddress)<div class="from-line">{{ $companyAddress }}</div>@endif
                @if($companyPhone || $companyEmail)
                <div class="from-line">
                    @if($companyPhone){{ $companyPhone }}@endif
                    @if($companyPhone && $companyEmail) &nbsp;&middot;&nbsp; @endif
                    @if($companyEmail){{ $companyEmail }}@endif
                </div>
                @endif
                @if($companyWebsite)<div class="from-line">{{ $companyWebsite }}</div>@endif
                @if($companyGst || $companyPan || $companyCin)
                <div class="from-gst">
                    @if($companyGst)<strong>GST No:</strong> {{ $companyGst }}@endif
                    @if($companyGst && ($companyPan || $companyCin)) &nbsp;&middot;&nbsp; @endif
                    @if($companyPan)<strong>PAN:</strong> {{ $companyPan }}@endif
                    @if($companyPan && $companyCin) &nbsp;&middot;&nbsp; @endif
                </div>
                @endif
            </td>
            <td style="width:50%;" class="party-to">
                <div class="to-label">Estimate To:</div>
                <div class="to-name">{{ $quotation->customer->name }}</div>
                @if($quotation->customer->company_name)<div class="to-line">{{ $quotation->customer->company_name }}</div>@endif
                @if(!empty($quotation->customer->address))<div class="to-line">{{ $quotation->customer->address }}</div>@endif
                @if($quotation->customer->mobile)<div class="to-line"><strong>{{ $quotation->customer->mobile }}</strong></div>@endif
                @if($quotation->customer->email)<div class="to-line"><strong>{{ $quotation->customer->email }}</strong></div>@endif
                @if(!empty($quotation->customer->gst_number))
                <div class="party-badge">GST: {{ $quotation->customer->gst_number }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

@if($quotation->subject)
<div class="est-subject-block">
    <div class="est-subject"><strong>Subject:</strong> {{ $quotation->subject }}</div>
</div>
@endif

{{-- ══════════════════════════════════════
     ITEMS TABLE
     ══════════════════════════════════════ --}}
<table class="sl-table {{ $showDesc ? '' : 'hide-desc' }}">
    <thead>
        <tr>
            <th class="tc" style="width:6%;">S.No</th>
            <th colspan="{{ $showDesc ? 2 : 1 }}">Item</th>
            <th style="width:12%;">Area / Mode</th>
            <th class="tr" style="width:13%;">Total</th>
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
            <td class="tc" style="padding-top:10px;"><span class="sl-num">{{ $loop->iteration }}</span></td>

            <td colspan="{{ $showDesc ? 2 : 1 }}" style="padding:0;">
                <table class="group-table">
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td style="width:{{ $showDesc ? '35%' : '100%' }};">
                                <div class="svc-item">
                                    <div class="svc-title">{{ $item->title }}</div>
                                </div>
                            </td>
                            @if($showDesc)
                            <td class="desc-col" style="width:65%;">
                                <div class="svc-item">
                                    @if($item->description)
                                        <div style="font-size:10px;color:#555;line-height:1.5;">{{ $item->description }}</div>
                                    @elseif($item->service && $item->service->description)
                                        <div style="font-size:10px;color:#555;line-height:1.5;opacity:.7;">
                                            {{ $item->service->description }}
                                            <span style="font-size:9px;color:#888;">&nbsp;(from service)</span>
                                        </div>
                                    @else
                                        <div style="color:#ccc;font-size:10px;">&mdash;</div>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>

            <td>
                @if($mode === 'sqft')
                    <div class="mode-label">SqFt &times; Rate</div>
                    @if($first->area_value)
                    <div class="mode-detail"><strong>{{ number_format($first->area_value, 2) }}</strong> sqft</div>
                    @endif
                    @if($first->rate_per_sft)
                    <div class="mode-rate">@ {!! $currSym !!}{{ number_format($first->rate_per_sft, 2) }}/sqft</div>
                    @endif
                @elseif($mode === 'nos')
                    <div class="mode-label">Nos &times; Rate</div>
                    @if($first->area_nos)
                    <div class="mode-detail"><strong>{{ number_format($first->area_nos, 0) }}</strong> nos</div>
                    @endif
                    @if($first->rate_per_nos)
                    <div class="mode-rate">@ {!! $currSym !!}{{ number_format($first->rate_per_nos, 2) }}/unit</div>
                    @endif
                @else
                    <div class="mode-label"></div>
                @endif
            </td>

            <td class="tr" style="padding-top:10px;">
                <span class="amt">{!! $currSym !!}{{ number_format($groupAmt, 2) }}</span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="{{ $colCount }}" style="text-align:center;color:#aaa;padding:20px;">No items found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- ══ SUMMARY ══ --}}
<table class="sum-table">
    <tr>
        <td colspan="{{ $sumColspan }}" class="sum-lbl">Subtotal</td>
        <td class="sum-val">{!! $currSym !!}{{ number_format($quotation->subtotal, 2) }}</td>
    </tr>
    @if((float) $quotation->tax_amount > 0)
    <tr>
        <td colspan="{{ $sumColspan }}" class="sum-lbl">Tax ({{ $quotation->tax_percent ?? 0 }}%)</td>
        <td class="sum-val">{!! $currSym !!}{{ number_format($quotation->tax_amount, 2) }}</td>
    </tr>
    @endif
    @if((float) $quotation->discount > 0)
    <tr>
        <td colspan="{{ $sumColspan }}" class="sum-lbl">
            Discount{{ $quotation->discount_percent > 0 ? ' (' . $quotation->discount_percent . '%)' : '' }}
        </td>
        <td class="sum-val ac">- {!! $currSym !!}{{ number_format($quotation->discount, 2) }}</td>
    </tr>
    @endif
    <tr class="sum-grand">
        <td colspan="{{ $sumColspan }}" class="sum-lbl">Grand Total</td>
        <td class="sum-val ac">{{ number_format($quotation->total, 2) }}</td>
    </tr>
</table>

{{-- ══ TERMS ══ --}}
@if($hasTerms || $hasGuarantee || $hasPayment)
<div class="terms-section">
    <div class="terms-title">Terms, Conditions &amp; Guarantee</div>
    @if($hasTerms)
    <div class="terms-block">
        <div class="terms-block-hdr">Terms &amp; Conditions</div>
        <div class="terms-block-body">{{ $termsText }}</div>
    </div>
    @endif
    @if($hasGuarantee)
    <div class="terms-block">
        <div class="terms-block-hdr">Guarantee &amp; Warranty</div>
        <div class="terms-block-body">{{ $guaranteeText }}</div>
    </div>
    @endif
    @if($hasPayment)
    <div class="terms-block">
        <div class="terms-block-hdr">Payment Terms</div>
        <div class="terms-block-body">{{ $paymentText }}</div>
    </div>
    @endif
</div>
@endif

{{-- ══ BANK DETAILS ══ --}}
@if($bankName || $bankAcNo || $bankUpi)
<div class="bank-outer">
    <table class="layout" style="border-collapse:collapse;">
        <tr>
            @if($bankName || $bankAcNo)
            <td style="vertical-align:top;padding-right:{{ ($bankUpi || $bankQrUrl) ? '4px' : '0' }};">
                <div class="bank-hdr">Bank Details</div>
                <div class="bank-body">
                    @if($bankName)<div><span class="bank-lbl">Bank:</span> <strong>{{ $bankName }}</strong>@if($bankBranch) &nbsp;&middot;&nbsp; {{ $bankBranch }}@endif</div>@endif
                    @if($bankAcName)<div><span class="bank-lbl">A/C Name:</span> <strong>{{ $bankAcName }}</strong></div>@endif
                    @if($bankAcNo)<div><span class="bank-lbl">A/C No:</span> <strong style="font-family:monospace;letter-spacing:1px;">{{ $bankAcNo }}</strong></div>@endif
                    @if($bankIfsc)<div><span class="bank-lbl">IFSC:</span> <strong style="font-family:monospace;">{{ $bankIfsc }}</strong></div>@endif
                </div>
            </td>
            @endif
            @if($bankUpi || $bankQrUrl)
            <td style="width:120px;vertical-align:top;padding-left:4px;">
                <div class="bank-hdr" style="text-align:center;">UPI / QR</div>
                <div class="bank-body" style="text-align:center;">
                    @if($bankQrUrl)<img src="{{ $bankQrUrl }}" style="width:75px;height:75px;object-fit:contain;display:block;margin:0 auto 4px;">@endif
                    @if($bankUpi)<div style="font-size:10px;font-weight:700;color:{{ $pdfHeaderColor }};word-break:break-all;">{{ $bankUpi }}</div>@endif
                </div>
            </td>
            @endif
        </tr>
    </table>
</div>
@endif

{{-- ══ SIGNATURE & SEAL ══ --}}
<div class="sign-outer">
    <table class="layout">
        <tr>
            <td style="width:50%;" class="sign-cell">
                @if($signatureUrl)
                    <img src="{{ $signatureUrl }}" alt="Signature" class="sign-seal-img">
                @else
                    <div class="sign-line"></div>
                @endif
                <div class="sign-lbl">Authorised By</div>
            </td>
            <td style="width:50%;" class="sign-cell">
                @if($sealUrl)
                    <img src="{{ $sealUrl }}" alt="Seal" class="sign-seal-img">
                @else
                    <div class="sign-line"></div>
                @endif
                <div class="sign-lbl">Company Seal</div>
            </td>
        </tr>
    </table>
</div>

{{-- ══ AUTHENTICITY ══ --}}
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

</div>{{-- .page --}}
</body>
</html>