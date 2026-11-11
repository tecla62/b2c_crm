<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt {{ $transaction->transaction_number }}</title>
    <style>
        body{margin:0;padding:24px 12px;background:#eef2f5;font-family:Arial,Helvetica,sans-serif;color:#0f172a;}
        .wrap{max-width:620px;margin:0 auto;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 12px 30px rgba(15,23,42,.10);}
        .head{background:#0f172a;color:#fff;padding:28px 32px;border-bottom:3px solid #22c55e;}
        .label{display:inline-block;color:#bbf7d0;background:rgba(34,197,94,.16);border-radius:999px;padding:5px 12px;font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;}
        .company{margin-top:16px;font-size:24px;font-weight:800;}
        .body{padding:30px 32px;}
        .box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:18px;margin-top:18px;}
        .row{display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #eef2f7;font-size:14px;}
        .row:last-child{border-bottom:0;}
        .row span:first-child{color:#64748b;}
        .row span:last-child{font-weight:700;text-align:right;}
        .amount{font-size:28px;font-weight:900;color:#16a34a;}
        .footer{padding:22px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;font-size:12px;color:#64748b;}
    </style>
</head>
<body>
    @php($accountSummary = $accountSummary ?? null)
    <div class="wrap">
        <div class="head">
            <span class="label">Payment Receipt</span>
            <div class="company">{{ setting('company_name', config('app.name')) }}</div>
        </div>
        <div class="body">
            <p>Dear {{ $transaction->customer?->name ?? 'Customer' }},</p>
            <p>We have received your payment. Please find the receipt PDF attached.</p>
            <div class="amount">{{ format_currency($transaction->amount) }}</div>
            <div class="box">
                <div class="row"><span>Receipt No</span><span>{{ $transaction->transaction_number }}</span></div>
                <div class="row"><span>Date</span><span>{{ $transaction->transaction_date->format('d M Y') }}</span></div>
                @if($transaction->invoice)
                <div class="row"><span>Invoice</span><span>{{ $transaction->invoice->invoice_number }}</span></div>
                @if($transaction->invoice->quotation)
                <div class="row"><span>Quotation</span><span>{{ $transaction->invoice->quotation->quotation_number }}</span></div>
                @endif
                @endif
                @if($accountSummary)
                <div class="row"><span>Invoice Total</span><span>{{ format_currency($accountSummary['invoice_total']) }}</span></div>
                <div class="row"><span>Paid Amount</span><span>{{ format_currency($transaction->amount) }}</span></div>
                <div class="row"><span>Total Paid</span><span>{{ format_currency($accountSummary['paid']) }}</span></div>
                <div class="row"><span>Balance Due</span><span>{{ format_currency($accountSummary['outstanding']) }}</span></div>
                @endif
                <div class="row"><span>Method</span><span>{{ str_replace('_',' ', ucfirst($transaction->payment_method)) }}</span></div>
                @if($transaction->reference_no)
                <div class="row"><span>Reference</span><span>{{ $transaction->reference_no }}</span></div>
                @endif
            </div>
        </div>
        <div class="footer">
            <strong>{{ setting('company_name', config('app.name')) }}</strong>
            @if(setting('company_email'))<br>{{ setting('company_email') }}@endif
        </div>
    </div>
</body>
</html>
