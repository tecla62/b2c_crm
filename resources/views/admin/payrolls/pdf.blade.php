@php
    $primaryColor     = setting('theme_color', '#0f172a');
    $pdfHeaderColor   = setting('pdf_header_color', $primaryColor);
    $currSym          = '&#8377;';

    $companyName     = setting('company_name',    config('app.name', 'Waterproofing CRM'));
    $companyTagline  = setting('company_tagline', '');
    $companyPhone    = setting('company_mobile',  '');
    $companyEmail    = setting('company_email',   '');
    $companyGst      = setting('company_gst',     '');

    $addrStreet  = setting('company_address', '');
    $addrCity    = setting('company_city',    '');
    $addrState   = setting('company_state',   '');
    $addrPincode = setting('company_pincode', '');
    $companyAddress = implode(', ', array_filter([$addrStreet, $addrCity, $addrState, $addrPincode]));

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

    $logoUrl      = get_base64_img('company_logo');
    $signatureUrl = get_base64_img('pdf_signature');
    $sealUrl      = get_base64_img('pdf_company_seal');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Payslip - {{ $payroll->payroll_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10px;
        line-height: 1.4;
        color: #1e293b;
        background: #fff;
        padding: 26px 30px;
    }
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .header-table td { vertical-align: top; }
    .logo-img { max-height: 55px; max-width: 170px; }
    .company-title { font-size: 18px; font-weight: bold; color: #0f172a; margin-bottom: 2px; }
    .company-tagline { font-size: 10px; color: #64748b; margin-bottom: 4px; }
    .company-meta { font-size: 9.5px; color: #475569; line-height: 1.35; }

    .doc-badge {
        display: inline-block;
        background: #0f172a;
        color: #ffffff;
        font-size: 12px;
        font-weight: bold;
        letter-spacing: 0.5px;
        padding: 5px 12px;
        border-radius: 4px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .doc-number { font-size: 15px; font-weight: bold; color: #0f172a; }
    .doc-meta { font-size: 10px; color: #475569; margin-top: 3px; }

    .meta-box {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 6px;
    }
    .meta-box td {
        padding: 9px 12px;
        vertical-align: top;
        border-right: 1px solid #e2e8f0;
        width: 50%;
    }
    .meta-box td:last-child { border-right: none; }
    .box-title {
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 5px;
        border-bottom: 1px solid #cbd5e1;
        padding-bottom: 3px;
    }
    .info-row { font-size: 10px; margin-bottom: 3px; }
    .info-label { color: #64748b; display: inline-block; width: 110px; font-weight: 500; }
    .info-val { color: #0f172a; font-weight: bold; }

    .section-title {
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        color: #0f172a;
        margin-bottom: 6px;
        margin-top: 10px;
    }

    .table-data {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
    }
    .table-data th {
        background: #0f172a;
        color: #ffffff;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 6px 7px;
        text-align: left;
        border: 1px solid #0f172a;
    }
    .table-data td {
        padding: 6px 7px;
        font-size: 9.5px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .table-data tr:nth-child(even) td { background: #fbfcfe; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }

    .calc-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
    }
    .calc-table td { vertical-align: top; }
    .calc-summary {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e2e8f0;
    }
    .calc-summary td {
        padding: 5px 9px;
        font-size: 10px;
        border-bottom: 1px solid #e2e8f0;
    }
    .calc-summary tr.highlight td {
        background: #f1f5f9;
        font-weight: bold;
    }
    .calc-summary tr.net-row td {
        background: #0f172a;
        color: #ffffff;
        font-size: 12px;
        font-weight: bold;
        padding: 7px 9px;
    }

    .amount-words-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 8px 12px;
        font-size: 10px;
        color: #334155;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .sign-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 25px;
    }
    .sign-table td {
        width: 50%;
        vertical-align: bottom;
        text-align: center;
        padding: 0 20px;
    }
    .sign-line {
        border-top: 1px solid #94a3b8;
        padding-top: 5px;
        font-size: 10px;
        font-weight: bold;
        color: #334155;
    }

    .badge-paid {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
    }
    .footer-note {
        text-align: center;
        font-size: 8.5px;
        color: #94a3b8;
        margin-top: 20px;
        border-top: 1px solid #f1f5f9;
        padding-top: 8px;
    }
</style>
</head>
<body>

{{-- Header --}}
<table class="header-table">
    <tr>
        <td style="width: 58%;">
            @if($logoUrl)
            <img src="{{ $logoUrl }}" class="logo-img" alt="Logo"><br>
            @endif
            <div class="company-title">{{ $companyName }}</div>
            @if($companyTagline)<div class="company-tagline">{{ $companyTagline }}</div>@endif
            <div class="company-meta">
                @if($companyAddress){{ $companyAddress }}<br>@endif
                @if($companyPhone)Mobile: {{ $companyPhone }} @endif
                @if($companyEmail)| Email: {{ $companyEmail }} @endif
                @if($companyGst)<br>GSTIN: {{ $companyGst }}@endif
            </div>
        </td>
        <td style="width: 42%; text-align: right;">
            @php
                $diffDays = $payroll->start_date->diffInDays($payroll->end_date) + 1;
                $cycleTitle = match($payroll->period_type) {
                    'weekly'         => 'WEEKLY WAGE SHEET (6 DAYS)',
                    'monthly'        => 'MONTHLY SALARY SHEET',
                    '40_days'        => '40-DAY SALARY SETTLEMENT SHEET',
                    '60_days'        => '60-DAY SALARY SETTLEMENT SHEET',
                    '70_days'        => '70-DAY SALARY SETTLEMENT SHEET',
                    'custom_days'    => "{$diffDays}-DAY SALARY SETTLEMENT SHEET",
                    'daily_ondemand' => 'DAILY / FLEXIBLE WAGE SHEET',
                    default          => strtoupper(str_replace('_', ' ', $payroll->period_type)) . ' WAGE SHEET',
                };
            @endphp
            <div class="doc-badge">{{ $cycleTitle }}</div>
            <div class="doc-number">{{ $payroll->payroll_number }}</div>
            <div class="doc-meta">
                <strong>Pay Period:</strong> {{ $payroll->start_date->format('d/m/Y') }} &mdash; {{ $payroll->end_date->format('d/m/Y') }}<br>
                <strong>Duration:</strong> {{ $diffDays }} Days Interval<br>
                <strong>Generated Date:</strong> {{ $payroll->created_at->format('d/m/Y') }}<br>
                <strong>Status:</strong>
                @if($payroll->status === 'paid')
                <span class="badge-paid">PAID</span>
                @else
                <strong>{{ strtoupper($payroll->status) }}</strong>
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- Worker & Payment Info Box --}}
<table class="meta-box">
    <tr>
        <td>
            <div class="box-title">Worker / Staff Information</div>
            <div class="info-row"><span class="info-label">Name:</span> <span class="info-val">{{ $payroll->employee?->name ?? 'Staff' }}</span></div>
            <div class="info-row"><span class="info-label">Employee Code:</span> <span class="info-val">{{ $payroll->employee?->employee_code }}</span></div>
            <div class="info-row"><span class="info-label">Designation:</span> <span class="info-val">{{ $payroll->employee?->designation ?: 'Site Technician' }}</span></div>
            <div class="info-row"><span class="info-label">Mobile:</span> <span class="info-val">{{ $payroll->employee?->mobile ?: 'N/A' }}</span></div>
            <div class="info-row"><span class="info-label">Wage Type:</span> <span class="info-val">{{ $payroll->employee?->salary_type_label }}</span></div>
        </td>
        <td>
            <div class="box-title">Disbursement & Bank Info</div>
            <div class="info-row"><span class="info-label">Bank Name:</span> <span class="info-val">{{ $payroll->employee?->bank_name ?: 'Cash Disbursement' }}</span></div>
            <div class="info-row"><span class="info-label">Account No:</span> <span class="info-val">{{ $payroll->employee?->account_number ?: '---' }}</span></div>
            <div class="info-row"><span class="info-label">IFSC Code:</span> <span class="info-val">{{ $payroll->employee?->ifsc_code ?: '---' }}</span></div>
            <div class="info-row"><span class="info-label">Payment Mode:</span> <span class="info-val">{{ $payroll->payment_method ?: 'Cash' }}</span></div>
            @if($payroll->payment_date)
            <div class="info-row"><span class="info-label">Paid Date:</span> <span class="info-val">{{ $payroll->payment_date->format('d/m/Y') }}</span></div>
            @endif
        </td>
    </tr>
</table>

{{-- 6-Day Site Attendance & Extra Duty Details Table --}}
<div class="section-title">Site Attendance & Extra Duty (OT) Breakdown</div>
<table class="table-data">
    <thead>
        <tr>
            <th style="width: 25px;" class="text-center">#</th>
            <th style="width: 75px;">Date & Day</th>
            <th>Site / Job Location</th>
            <th style="width: 65px;" class="text-center">Shift Times</th>
            <th style="width: 50px;" class="text-center">Status</th>
            <th style="width: 40px;" class="text-center">Hrs</th>
            <th>Extra Duty (OT) Details</th>
            <th style="width: 55px;" class="text-right">OT Rate</th>
            <th style="width: 65px;" class="text-right">OT Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payroll->items as $idx => $item)
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td>
                <strong>{{ $item->date->format('d/m/Y') }}</strong><br>
                <span style="font-size: 8.5px; color: #64748b;">{{ $item->date->format('l') }}</span>
            </td>
            <td>
                <strong>{{ $item->site_name ?: 'Main Site' }}</strong>
                @if($item->notes)<br><span style="font-size: 8.5px; color: #64748b;">{{ $item->notes }}</span>@endif
            </td>
            <td class="text-center" style="font-size: 8.5px;">
                {{ $item->punch_in ? \Carbon\Carbon::parse($item->punch_in)->format('h:i A') : '--' }} -
                {{ $item->punch_out ? \Carbon\Carbon::parse($item->punch_out)->format('h:i A') : '--' }}
            </td>
            <td class="text-center">
                <strong>{{ ucfirst($item->status) }}</strong>
            </td>
            <td class="text-center">{{ $item->regular_hours }}</td>
            <td>
                @if($item->extra_duty_hours > 0)
                <strong>{{ $item->extra_duty_hours }} hrs</strong>
                @if($item->extra_duty_job) - {{ $item->extra_duty_job }}@endif
                @else
                <span style="color: #94a3b8;">&mdash;</span>
                @endif
            </td>
            <td class="text-right">
                @if($item->extra_duty_hours > 0)
                {!! $currSym !!}{{ number_format($item->extra_duty_rate, 2) }}
                @else
                &mdash;
                @endif
            </td>
            <td class="text-right" style="font-weight: bold;">
                @if($item->extra_duty_amount > 0)
                {!! $currSym !!}{{ number_format($item->extra_duty_amount, 2) }}
                @else
                {!! $currSym !!}0.00
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center" style="padding: 12px; color: #64748b;">No individual attendance rows recorded.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="background: #f8fafc; font-weight: bold;">
            <td colspan="6" class="text-right">Total Extra Duty OT:</td>
            <td colspan="2"><strong>{{ $payroll->total_extra_duty_hours }} Hours Overtime</strong></td>
            <td class="text-right" style="color: #0f172a;">{!! $currSym !!}{{ number_format($payroll->total_extra_duty_amount, 2) }}</td>
        </tr>
    </tfoot>
</table>

{{-- Calculations & Earnings Box --}}
<table class="calc-table">
    <tr>
        <td style="width: 52%; padding-right: 15px;">
            <div class="box-title">Work Attendance Summary</div>
            <table class="calc-summary" style="margin-bottom: 10px;">
                <tr>
                    <td>Total Standard Days:</td>
                    <td class="text-right"><strong>6 Days Cycle</strong></td>
                </tr>
                <tr>
                    <td>Days Present:</td>
                    <td class="text-right"><strong>{{ $payroll->days_present }}</strong></td>
                </tr>
                <tr>
                    <td>Half Days:</td>
                    <td class="text-right"><strong>{{ $payroll->days_half_day }}</strong></td>
                </tr>
                <tr>
                    <td>Days Absent / Leave:</td>
                    <td class="text-right"><strong>{{ $payroll->days_absent }}</strong></td>
                </tr>
                <tr class="highlight">
                    <td>Total Payable Working Days:</td>
                    <td class="text-right"><strong>{{ $payroll->total_working_days }} Days</strong></td>
                </tr>
            </table>

            @if($payroll->notes)
            <div style="font-size: 9.5px; color: #475569; padding: 6px; background: #f8fafc; border: 1px solid #e2e8f0;">
                <strong>Notes:</strong> {{ $payroll->notes }}
            </div>
            @endif
        </td>

        <td style="width: 48%;">
            <div class="box-title">Wages & Payout Calculation</div>
            <table class="calc-summary">
                <tr>
                    <td>Base Salary Rate:</td>
                    <td class="text-right">{!! $currSym !!}{{ number_format($payroll->base_salary_rate, 2) }}</td>
                </tr>
                <tr>
                    <td>Base Wages Earned:</td>
                    <td class="text-right"><strong>{!! $currSym !!}{{ number_format($payroll->base_salary_earned, 2) }}</strong></td>
                </tr>
                <tr>
                    <td>Extra Duty OT ({{ $payroll->total_extra_duty_hours }} hrs):</td>
                    <td class="text-right" style="color: #16a34a;"><strong>+ {!! $currSym !!}{{ number_format($payroll->total_extra_duty_amount, 2) }}</strong></td>
                </tr>
                @if($payroll->allowance_amount > 0)
                <tr>
                    <td>Allowance ({{ $payroll->allowance_notes ?: 'Bonus' }}):</td>
                    <td class="text-right" style="color: #16a34a;">+ {!! $currSym !!}{{ number_format($payroll->allowance_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="highlight">
                    <td>Gross Wages:</td>
                    <td class="text-right"><strong>{!! $currSym !!}{{ number_format($payroll->gross_amount, 2) }}</strong></td>
                </tr>
                @if($payroll->deduction_amount > 0)
                <tr>
                    <td>Deduction ({{ $payroll->deduction_notes ?: 'Advance' }}):</td>
                    <td class="text-right" style="color: #dc2626;">- {!! $currSym !!}{{ number_format($payroll->deduction_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="net-row">
                    <td>NET PAYABLE SALARY:</td>
                    <td class="text-right">{!! $currSym !!}{{ number_format($payroll->net_amount, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Amount in Words --}}
<div class="amount-words-box">
    <strong>Net Pay in Words:</strong> {{ amount_to_words_inr($payroll->net_amount) }}
</div>

{{-- Signatures --}}
<table class="sign-table">
    <tr>
        <td>
            <br><br><br>
            <div class="sign-line">Employee / Worker Signature</div>
            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">{{ $payroll->employee?->name }}</div>
        </td>
        <td>
            @if($signatureUrl)
            <img src="{{ $signatureUrl }}" style="max-height: 45px; margin-bottom: 4px;" alt="Signature"><br>
            @else
            <br><br><br>
            @endif
            <div class="sign-line">Authorized Signatory</div>
            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">For {{ $companyName }}</div>
        </td>
    </tr>
</table>

<div class="footer-note">
    This document is a computer-generated weekly wage statement and salary slip issued by {{ $companyName }}. Generated on {{ date('d/m/Y h:i A') }}.
</div>

</body>
</html>
