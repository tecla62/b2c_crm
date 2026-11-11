<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice | {{ $invoice->invoice_number }}</title>
    <style>
        body { margin:0; padding:24px 12px; background:#eef2f5; font-family:Arial, Helvetica, sans-serif; color:#0f172a; }
        .wrap { max-width:640px; margin:0 auto; background:#fff; border-radius:18px; overflow:hidden; box-shadow:0 12px 30px rgba(15,23,42,.10); }
        .head { background:#0f172a; color:#fff; padding:28px 32px; border-bottom:3px solid #22c55e; }
        .label { display:inline-block; color:#bbf7d0; background:rgba(34,197,94,.16); border-radius:999px; padding:5px 12px; font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; }
        .company { margin-top:16px; font-size:24px; font-weight:800; }
        .number { margin-top:5px; color:#cbd5e1; font-size:13px; }
        .body { padding:30px 32px; }
        .message { white-space:pre-line; background:#f8fafc; border:1px solid #e2e8f0; border-left:4px solid #22c55e; border-radius:12px; padding:18px; font-size:14px; line-height:1.65; color:#334155; }
        .details { margin-top:24px; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; }
        .row { display:flex; justify-content:space-between; gap:16px; padding:13px 18px; border-bottom:1px solid #eef2f7; font-size:14px; }
        .row:last-child { border-bottom:none; }
        .row span:first-child { color:#64748b; }
        .row span:last-child { font-weight:700; text-align:right; }
        .total { background:#f0fdf4; color:#166534; }
        .attach { margin-top:24px; padding:16px 18px; background:#f8fafc; border-radius:12px; font-size:14px; color:#475569; }
        .footer { padding:22px 32px; background:#f8fafc; border-top:1px solid #e2e8f0; text-align:center; font-size:12px; color:#64748b; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="head">
            <span class="label">Invoice</span>
            <div class="company">{{ setting('company_name', config('app.name')) }}</div>
            <div class="number">{{ $invoice->invoice_number }}</div>
        </div>
        <div class="body">
            <div class="message">{!! nl2br(e($body)) !!}</div>

            <div class="details">
                <div class="row"><span>Invoice number</span><span>{{ $invoice->invoice_number }}</span></div>
                <div class="row"><span>Date issued</span><span>{{ $invoice->date->format('d F Y') }}</span></div>
                @if($invoice->due_date)
                <div class="row"><span>Due date</span><span>{{ $invoice->due_date->format('d F Y') }}</span></div>
                @endif
                @if($invoice->subject)
                <div class="row"><span>Subject</span><span>{{ $invoice->subject }}</span></div>
                @endif
                <div class="row total"><span>Total amount</span><span>{{ format_currency($invoice->total) }}</span></div>
            </div>

            <div class="attach">
                The detailed invoice PDF is attached with this email.
            </div>
        </div>
        <div class="footer">
            <strong>{{ setting('company_name', config('app.name')) }}</strong>
            @if(setting('company_email'))<br>{{ setting('company_email') }}@endif
            @if(setting('company_mobile'))<br>{{ setting('company_mobile') }}@endif
        </div>
    </div>
</body>
</html>
