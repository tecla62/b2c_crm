@php
    $pdfMode = $pdfMode ?? false;
    $accountSummary = $accountSummary ?? null;

    $primaryColor   = setting('theme_color', '#4f46e5');
    $pdfHeaderColor = setting('pdf_header_color', $primaryColor);

    $companyName     = setting('company_name', config('app.name'));
    $companyTagline  = setting('company_tagline', '');
    $companyPhone    = setting('company_mobile', '');
    $companyAltPhone = setting('company_alt_mobile', '');
    $companyEmail    = setting('company_email', '');
    $companyWebsite  = setting('company_website', '');
    $companyOwner    = setting('company_owner_name', '');
    $companyGst      = setting('company_gst', '');
    $companyPan      = setting('company_pan', '');
    $companyCin      = setting('company_cin', '');

    $addrStreet  = setting('company_address', '');
    $addrCity    = setting('company_city', '');
    $addrState   = setting('company_state', '');
    $addrPincode = setting('company_pincode', '');
    $companyAddress = implode(', ', array_filter([$addrStreet, $addrCity, $addrState, $addrPincode]));

    $bankName   = setting('bank_name', '');
    $bankAcName = setting('bank_account_name', '');
    $bankAcNo   = setting('bank_account_number', '');
    $bankIfsc   = setting('bank_ifsc', '');
    $bankBranch = setting('bank_branch', '');
    $bankUpi    = setting('bank_upi_id', '');

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

    $bankQrUrl      = get_base64_img('bank_qr_code');
    $logoUrl        = get_base64_img('company_logo');
    $invoiceLogoUrl = get_base64_img('pdf_invoice_logo');
    $headerLogoUrl  = $invoiceLogoUrl ?: $logoUrl;
    $watermarkUrl   = get_base64_img('pdf_watermark_image');
    $signatureUrl   = get_base64_img('pdf_signature');
    $sealUrl        = get_base64_img('pdf_company_seal');
    $pdfFooterText  = setting('pdf_footer_text', 'This is a computer generated document.');

    $customer = $transaction->customer;
    $invoice = $transaction->invoice;
    $paidAmount = (float) $transaction->amount;
    $invoiceTotal = $accountSummary ? (float) $accountSummary['invoice_total'] : (float) ($invoice?->total ?? $paidAmount);
    $totalPaid = $accountSummary ? (float) $accountSummary['paid'] : $paidAmount;
    $balanceAmount = $accountSummary ? (float) $accountSummary['outstanding'] : max(0, $invoiceTotal - $paidAmount);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $transaction->transaction_number }} Receipt</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

@page {
    size: A4 portrait;
    margin: 12mm 12mm 18mm 12mm;
}

html, body {
    margin: 0;
    padding: 24px;
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 11px;
    color: #222;
    background: #fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    line-height: 1.45;
}

.page-border {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border: 2px solid #111;
    z-index: -1000;
}

.watermark {
    position: fixed;
    top: 22%;
    left: 5%;
    width: 90%;
    text-align: center;
    opacity: 0.06;
    z-index: -100;
}
.watermark img { max-width: 420px; height: auto; }
.watermark-text { font-size: 60px; font-weight: 900; color: {{ $pdfHeaderColor }}; letter-spacing: 4px; }

table.layout { width: 100%; border-collapse: collapse; table-layout: fixed; }
table.layout td { vertical-align: top; }

.hdr-wrap {
    width: 100%;
    border-bottom: 2px solid {{ $pdfHeaderColor }};
    padding-bottom: 10px;
    margin-bottom: 16px;
}
.hdr-logo img {
    height: 68px;
    max-width: 220px;
    object-fit: contain;
    display: block;
}
.hdr-logo-text {
    font-size: 17px;
    font-weight: 800;
    color: {{ $pdfHeaderColor }};
    word-break: break-word;
}
.hdr-meta { text-align: right; }
.receipt-number { font-size: 15px; font-weight: 800; color: #111; text-transform: uppercase; margin-bottom: 4px; }
.receipt-meta-line { font-size: 10px; color: #666; line-height: 1.65; }
.receipt-meta-line strong { color: #333; font-weight: 700; }
.receipt-status { display: inline-block; margin-top: 8px; padding: 3px 9px; border-radius: 3px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; background: {{ $pdfHeaderColor }}; color: #fff; }

.party-wrap { margin-bottom: 18px; }
.party-from { padding-right: 10px; }
.party-to { padding-left: 10px; text-align: right; }
.from-name, .to-name { font-size: 13px; font-weight: 700; color: #111; margin-bottom: 2px; }
.from-line, .to-line { font-size: 10px; color: #555; line-height: 1.6; word-break: break-word; }
.from-gst { margin-top: 3px; font-size: 10px; color: #555; word-break: break-word; }
.to-label { font-size: 9px; font-weight: 700; color: #888; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .8px; }
.party-badge { font-size: 10px; font-weight: 700; color: {{ $pdfHeaderColor }}; margin-top: 4px; }

.sl-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 0; }
.sl-table thead tr { background: {{ $pdfHeaderColor }}; }
.sl-table thead th { padding: 9px 7px; color: #fff; font-size: 9.5px; font-weight: 700; text-align: left; text-transform: uppercase; letter-spacing: .3px; border: 1px solid {{ $pdfHeaderColor }}; white-space: normal; word-break: break-word; }
.sl-table tbody td { padding: 9px 7px; vertical-align: top; border: 1px solid #e0e0e0; font-size: 10.5px; color: #333; word-break: break-word; overflow-wrap: break-word; }
.sl-table tbody tr:nth-child(even) { background: #f9fafb; }
.sl-table tbody td.tc { text-align: center; }
.sl-table tbody td.tr { text-align: right; }
.amount { font-size: 11.5px; font-weight: 700; color: #111; white-space: nowrap; }

.sum-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.sum-table td { padding: 8px 10px; border: 1px solid #e0e0e0; font-size: 10.5px; }
.sum-lbl { text-align: right; font-weight: 700; color: #555; background: #fff; }
.sum-val { text-align: right; font-weight: 700; color: #111; background: #fff; white-space: nowrap; }
.sum-val.ac { color: {{ $pdfHeaderColor }}; }
.sum-grand td { font-size: 12.5px; font-weight: 800; border-top: 2px solid {{ $pdfHeaderColor }}; background: #f8fafc; }

.terms-section { margin: 20px 0 16px; }
.terms-title { font-size: 11.5px; font-weight: 800; text-transform: uppercase; color: {{ $pdfHeaderColor }}; margin-bottom: 10px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
.terms-block { margin-bottom: 10px; page-break-inside: avoid; }
.terms-block-hdr { font-size: 11px; font-weight: 700; color: #111; margin-bottom: 3px; }
.terms-block-body { font-size: 10px; color: #555; line-height: 1.6; white-space: pre-line; }

.bank-outer { margin-bottom: 20px; }
.bank-hdr { padding: 7px 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; background: {{ $pdfHeaderColor }}; color: #fff; }
.bank-body { padding: 10px 12px; font-size: 10.5px; color: #444; line-height: 1.8; border: 1px solid #e0e0e0; background: #f8fafc; }
.bank-lbl { color: #94a3b8; font-size: 10px; }

.sign-outer { margin: 24px 0 14px; padding-top: 18px; border-top: 1px solid #e2e8f0; page-break-inside: avoid; }
.sign-cell { text-align: center; vertical-align: bottom; }
.sign-seal-img { height: 84px; max-width: 180px; object-fit: contain; margin: 0 auto 7px; display: block; }
.sign-line { border-bottom: 1px solid #333; height: 32px; width: 145px; margin: 0 auto 7px; }
.sign-lbl { font-size: 9.5px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: .3px; }

.auth-box { margin-bottom: 16px; padding: 9px 13px; background: #f8fafc; border-left: 4px solid {{ $pdfHeaderColor }}; page-break-inside: avoid; }
.auth-title { font-size: 10px; font-weight: 700; color: {{ $pdfHeaderColor }}; margin-bottom: 3px; text-transform: uppercase; }
.auth-body { font-size: 10px; color: #555; line-height: 1.6; }

footer { position: fixed; bottom: -52px; left: 0; right: 0; height: 42px; }
.foot-inner { text-align: center; border-top: 1px solid #e0e0e0; padding-top: 7px; }
.foot-stmt { font-size: 9.5px; color: #888; font-style: italic; margin-bottom: 2px; }
.foot-meta { font-size: 9px; color: #aaa; }
</style>
</head>
<body>

<div class="page-border"></div>

<div class="watermark">
    @if($watermarkUrl)
        <img src="{{ $watermarkUrl }}" alt="">
    @elseif($headerLogoUrl)
        <img src="{{ $headerLogoUrl }}" alt="{{ $companyName }}">
    @else
        <div class="watermark-text">{{ $companyName }}</div>
    @endif
</div>

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
    <div class="hdr-wrap">
        <table class="layout">
            <tr>
                <td style="width:48%; vertical-align: middle;" class="hdr-logo">
                    @if($headerLogoUrl)
                        <img src="{{ $headerLogoUrl }}" alt="{{ $companyName }}">
                    @else
                        <div class="hdr-logo-text">{{ $companyName }}</div>
                    @endif
                </td>
                <td style="width:52%;" class="hdr-meta">
                    <div class="receipt-number">{{ strtoupper($transaction->transaction_number) }}</div>
                    <div class="receipt-meta-line"><strong>Receipt Date:</strong> {{ $transaction->transaction_date->format('d M Y') }}</div>
                    @if($invoice)
                    <div class="receipt-meta-line"><strong>Invoice:</strong> {{ $invoice->invoice_number }}</div>
                    @endif
                    @if($invoice?->quotation)
                    <div class="receipt-meta-line"><strong>Quotation:</strong> {{ $invoice->quotation->quotation_number }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

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
                    <div class="to-label">Received From:</div>
                    <div class="to-name">{{ $customer?->name ?? 'Customer' }}</div>
                    @if($customer?->company_name)<div class="to-line">{{ $customer->company_name }}</div>@endif
                    @if(!empty($customer?->address))<div class="to-line">{{ $customer->address }}</div>@endif
                    @if($customer?->mobile)<div class="to-line"><strong>{{ $customer->mobile }}</strong></div>@endif
                    @if($customer?->email)<div class="to-line"><strong>{{ $customer->email }}</strong></div>@endif
                    @if(!empty($customer?->gst_number))
                    <div class="party-badge">GST: {{ $customer->gst_number }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="sl-table">
        <thead>
            <tr>
                <th class="tc" style="width:8%;">#</th>
                <th>Details</th>
                <th style="width:18%;">Mode</th>
                <th class="tr" style="width:18%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="tc">1</td>
                <td>
                    <div><strong>{{ $invoice?->invoice_number ?? 'Direct Payment' }}</strong></div>
                    <div style="font-size:10px;color:#555;line-height:1.5;">Received against {{ $invoice ? 'invoice ' . $invoice->invoice_number : 'direct payment' }} on {{ $transaction->transaction_date->format('d M Y') }}.</div>
                </td>
                <td>{{ str_replace('_', ' ', ucfirst($transaction->payment_method)) }}</td>
                <td class="tr amount">{{ format_currency($paidAmount) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="sum-table">
        <tr>
            <td colspan="4" class="sum-lbl">Invoice Total</td>
            <td class="sum-val">{{ format_currency($invoiceTotal) }}</td>
        </tr>
        <tr class="sum-grand">
            <td colspan="4" class="sum-lbl">Paid Amount</td>
            <td class="sum-val ac">{{ format_currency($paidAmount) }}</td>
        </tr>
        @if($accountSummary)
        <tr>
            <td colspan="4" class="sum-lbl">Total Collected</td>
            <td class="sum-val">{{ format_currency($totalPaid) }}</td>
        </tr>
        <tr class="sum-grand">
            <td colspan="4" class="sum-lbl">Balance Due</td>
            <td class="sum-val ac">{{ format_currency($balanceAmount) }}</td>
        </tr>
        @endif
    </table>

    @if($transaction->notes)
    <div class="terms-section">
        <div class="terms-title">Receipt Notes</div>
        <div class="terms-block">
            <div class="terms-block-hdr">Notes</div>
            <div class="terms-block-body">{{ $transaction->notes }}</div>
        </div>
    </div>
    @endif

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

    <div class="sign-outer">
        <table class="layout">
            <tr>
                <td style="width:50%;" class="sign-cell">
                    @if($signatureUrl)
                        <img src="{{ $signatureUrl }}" alt="Authorised Signature" class="sign-seal-img">
                    @else
                        <div class="sign-line"></div>
                    @endif
                    <div class="sign-lbl">Authorised By</div>
                </td>
                <td style="width:50%;" class="sign-cell">
                    @if($sealUrl)
                        <img src="{{ $sealUrl }}" alt="Company Seal" class="sign-seal-img">
                    @else
                        <div class="sign-line"></div>
                    @endif
                    <div class="sign-lbl">Company Seal</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="auth-box">
        <div class="auth-title">Payment Confirmation</div>
        <div class="auth-body">
            This receipt confirms payment received from <strong>{{ $customer?->name ?? 'Customer' }}</strong>
            @if($invoice)
                against invoice <strong>{{ $invoice->invoice_number }}</strong>
            @endif
            by {{ $companyName }}.
        </div>
    </div>
</div>
</body>
</html>
